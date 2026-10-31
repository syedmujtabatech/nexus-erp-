<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Auth::user()->companies;
        return view('company.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'currency' => 'required|string',
            'plan' => 'nullable|in:trial,basic,advance,premium',
        ]);

        $user = Auth::user();
        $plan = $request->plan ?? 'trial';

        $limits = match($plan) {
            'basic' => ['users' => 5, 'invoices' => 50, 'days' => 30],
            'advance' => ['users' => 20, 'invoices' => 500, 'days' => 365],
            'premium' => ['users' => 9999, 'invoices' => 9999, 'days' => 365],
            default => ['users' => 3, 'invoices' => 20, 'days' => 14], // trial
        };

        $company = Company::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address,
            'currency' => $request->currency,
            'plan' => $plan,
            'max_users' => $limits['users'],
            'max_invoices' => $limits['invoices'],
            'plan_expires_at' => now()->addDays($limits['days']),
        ]);

        $user->companies()->attach($company->id, ['role_id' => 2]);
        $user->update(['current_company_id' => $company->id]);

        ActivityLogger::log('create_company', "Created new company: {$company->name} ({$company->currency}) with plan: {$company->plan}", $company->id);

        return redirect()->route('dashboard')->with('success', "Company '{$company->name}' created and set as active workspace.");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'plan' => 'nullable|in:trial,basic,advance,premium',
        ]);

        $company = Company::findOrFail($id);

        if (!Auth::user()->isSuperAdmin() && $company->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized to modify this company.');
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'currency' => $request->currency,
            'plan' => $request->plan ?? $company->plan,
        ]);

        ActivityLogger::log('update_company', "Updated company details for {$company->name}", $company->id);

        return redirect()->back()->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:trial,basic,advance,premium',
        ]);

        $company = Company::findOrFail($id);

        if (!Auth::user()->isSuperAdmin() && $company->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized to change subscription plan.');
        }

        $limits = match($request->plan) {
            'basic' => ['users' => 5, 'invoices' => 50, 'days' => 30],
            'advance' => ['users' => 20, 'invoices' => 500, 'days' => 365],
            'premium' => ['users' => 9999, 'invoices' => 9999, 'days' => 365],
            default => ['users' => 3, 'invoices' => 20, 'days' => 14], // trial
        };

        $company->update([
            'plan' => $request->plan,
            'max_users' => $limits['users'],
            'max_invoices' => $limits['invoices'],
            'plan_expires_at' => now()->addDays($limits['days']),
        ]);

        ActivityLogger::log('upgrade_plan', "Upgraded subscription plan for {$company->name} to " . strtoupper($request->plan), $company->id);

        return redirect()->back()->with('success', "Subscription plan updated to " . strtoupper($request->plan) . " successfully.");
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        if (!Auth::user()->isSuperAdmin() && $company->user_id != Auth::id()) {
            return redirect()->back()->with('error', 'Unauthorized to delete this company.');
        }

        $user = Auth::user();
        if ($user->companies()->count() <= 1 && !$user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'You must maintain at least one company workspace.');
        }

        $name = $company->name;
        $company->delete();

        if ($user->current_company_id == $id) {
            $another = $user->companies()->first();
            $user->update(['current_company_id' => $another ? $another->id : null]);
        }

        ActivityLogger::log('delete_company', "Deleted company: {$name}");

        return redirect()->route('companies.index')->with('success', "Company '{$name}' deleted successfully.");
    }

    public function dismissModal()
    {
        session(['company_modal_dismissed' => true]);
        session()->forget('show_company_select_modal');
        return response()->json(['status' => 'dismissed']);
    }
}
