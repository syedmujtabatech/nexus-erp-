<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Services\SequenceService;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class CRMController extends Controller
{
    // --- CUSTOMERS ---
    public function indexCustomers()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $customers = Customer::where('company_id', $companyId)->withCount(['invoices', 'leads'])->latest()->get();
        return view('crm.customers.index', compact('customers'));
    }

    public function showCustomer($id)
    {
        $customer = Customer::with([
            'invoices' => function($q) { $q->latest(); },
            'invoices.items',
            'quotations' => function($q) { $q->latest(); },
            'leads' => function($q) { $q->latest(); }
        ])->findOrFail($id);

        $totalBilled = $customer->invoices->sum('total');
        $totalPaid = $customer->invoices->sum('paid_amount');
        $balanceDue = $totalBilled - $totalPaid;

        return view('crm.customers.show', compact('customer', 'totalBilled', 'totalPaid', 'balanceDue'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create([
            'company_id' => Auth::user()->current_company_id,
            'created_by_user_id' => Auth::id(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        ActivityLogger::log('create_customer', "Created customer: {$customer->name}");

        return redirect()->back()->with('success', 'Customer created successfully.');
    }

    public function updateCustomer(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        ActivityLogger::log('update_customer', "Updated customer details: {$customer->name}");

        return redirect()->back()->with('success', 'Customer updated successfully.');
    }

    public function destroyCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $name = $customer->name;

        // Check if has invoices
        if ($customer->invoices()->count() > 0) {
            return redirect()->back()->with('error', "Cannot delete customer '{$name}' because they have associated invoices.");
        }

        $customer->delete();
        ActivityLogger::log('delete_customer', "Deleted customer: {$name}");

        return redirect()->route('customers.index')->with('success', "Customer '{$name}' deleted successfully.");
    }

    // --- LEADS ---
    public function indexLeads()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $leads = Lead::where('company_id', $companyId)->with('customer')->latest()->get();
        $customers = Customer::where('company_id', $companyId)->get();
        return view('crm.leads.index', compact('leads', 'customers'));
    }

    public function showLead($id)
    {
        $lead = Lead::with('customer')->findOrFail($id);
        return view('crm.leads.show', compact('lead'));
    }

    public function storeLead(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $lead = Lead::create([
            'company_id' => Auth::user()->current_company_id,
            'created_by_user_id' => Auth::id(),
            'title' => $request->title,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'value' => $request->value,
            'status' => $request->status ?? 'new',
            'notes' => $request->notes,
        ]);

        ActivityLogger::log('create_lead', "Created new lead: {$lead->title} ({$lead->contact_name})");

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    public function updateLead(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'value' => 'required|numeric',
            'status' => 'required|in:new,contacted,qualified,proposal_sent,converted,lost',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update([
            'title' => $request->title,
            'contact_name' => $request->contact_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'value' => $request->value,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        ActivityLogger::log('update_lead', "Updated lead: {$lead->title} [Status: {$lead->status}]");

        return redirect()->back()->with('success', 'Lead updated successfully.');
    }

    public function destroyLead($id)
    {
        $lead = Lead::findOrFail($id);
        $title = $lead->title;
        $lead->delete();

        ActivityLogger::log('delete_lead', "Deleted lead: {$title}");

        return redirect()->route('leads.index')->with('success', "Lead '{$title}' deleted successfully.");
    }

    // Convert Lead -> Customer & Start Quotation
    public function convertLead(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        // 1. Create Customer from Lead
        $customer = Customer::create([
            'company_id' => Auth::user()->current_company_id,
            'created_by_user_id' => Auth::id(),
            'name' => $lead->contact_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company_name' => $lead->title,
        ]);

        // 2. Update Lead status
        $lead->update([
            'status' => 'converted',
            'customer_id' => $customer->id,
        ]);

        ActivityLogger::log('convert_lead', "Converted lead '{$lead->title}' into Customer: {$customer->name}");

        return redirect()->route('quotations.create', ['customer_id' => $customer->id])->with('success', 'Lead converted to Customer successfully! You can now generate a quotation.');
    }
}
