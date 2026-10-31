<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceItem;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SequenceService;
use App\Services\ActivityLogger;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    // ==========================================
    // --- QUOTATIONS ---
    // ==========================================
    public function indexQuotations()
    {
        $quotations = Quotation::with('customer')->latest()->get();
        return view('billing.quotations.index', compact('quotations'));
    }

    public function createQuotation(Request $request)
    {
        $customers = Customer::all();
        $products = Product::all();
        $selectedCustomerId = $request->get('customer_id');
        $nextSeq = SequenceService::getNextNumber(Auth::user()->current_company_id, 'quotation', 'QT-');
        return view('billing.quotations.create', compact('customers', 'products', 'selectedCustomerId', 'nextSeq'));
    }

    public function showQuotation($id)
    {
        $quotation = Quotation::with(['customer', 'items.product'])->findOrFail($id);
        $company = Auth::user()->currentCompany;
        return view('billing.quotations.show', compact('quotation', 'company'));
    }

    public function storeQuotation(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        $companyId = Auth::user()->current_company_id;
        $seq = SequenceService::getNextNumber($companyId, 'quotation', 'QT-');

        $quotation = Quotation::create([
            'company_id' => $companyId,
            'customer_id' => $request->customer_id,
            'created_by_user_id' => Auth::id(),
            'quotation_number' => $seq['formatted'],
            'sequence_number' => $seq['sequence_number'],
            'date' => $request->date,
            'valid_until' => $request->valid_until,
            'notes' => $request->notes,
            'status' => 'draft',
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $qty = $item['quantity'] ?? 1;
            $price = $item['unit_price'] ?? 0;
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $quotation->items()->create([
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'] ?? 'Item',
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ]);
        }

        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $grandTotal = max(0, ($subtotal - $discount) + $tax);

        $quotation->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $grandTotal,
        ]);

        ActivityLogger::log('create_quotation', "Created quotation {$quotation->quotation_number} for total {$grandTotal}");

        return redirect()->route('quotations.show', $quotation->id)->with('success', "Quotation {$quotation->quotation_number} generated successfully.");
    }

    public function editQuotation($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        $customers = Customer::all();
        $products = Product::all();
        return view('billing.quotations.edit', compact('quotation', 'customers', 'products'));
    }

    public function updateQuotation(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'status' => 'required|in:draft,sent,accepted,declined,converted',
        ]);

        $quotation = Quotation::findOrFail($id);

        $quotation->update([
            'customer_id' => $request->customer_id,
            'date' => $request->date,
            'valid_until' => $request->valid_until,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Replace items
        $quotation->items()->delete();

        $subtotal = 0;
        foreach ($request->items as $item) {
            $qty = $item['quantity'] ?? 1;
            $price = $item['unit_price'] ?? 0;
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $quotation->items()->create([
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'] ?? 'Item',
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ]);
        }

        $discount = $request->discount ?? 0;
        $tax = $request->tax ?? 0;
        $grandTotal = max(0, ($subtotal - $discount) + $tax);

        $quotation->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $grandTotal,
        ]);

        ActivityLogger::log('update_quotation', "Updated quotation {$quotation->quotation_number}");

        return redirect()->route('quotations.show', $quotation->id)->with('success', "Quotation {$quotation->quotation_number} updated successfully.");
    }

    public function destroyQuotation($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quoteNumber = $quotation->quotation_number;
        // Triggers boot model deleting event -> calls SequenceService::recycleNumber()
        $quotation->delete();

        ActivityLogger::log('delete_quotation', "Deleted quotation {$quoteNumber} (sequence recycled)");

        return redirect()->route('quotations.index')->with('success', "Quotation {$quoteNumber} deleted. Sequence number has been recycled for the next quotation!");
    }

    // Convert Quotation -> Invoice
    public function convertQuotationToInvoice($id)
    {
        $quotation = Quotation::with('items')->findOrFail($id);
        $companyId = Auth::user()->current_company_id;
        $seq = SequenceService::getNextNumber($companyId, 'invoice', 'INV-');

        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'created_by_user_id' => Auth::id(),
            'invoice_number' => $seq['formatted'],
            'sequence_number' => $seq['sequence_number'],
            'date' => now(),
            'due_date' => now()->addDays(15),
            'subtotal' => $quotation->subtotal,
            'discount' => $quotation->discount,
            'tax' => $quotation->tax,
            'total' => $quotation->total,
            'status' => 'unpaid',
            'notes' => $quotation->notes,
        ]);

        foreach ($quotation->items as $item) {
            $cost = 0;
            if ($item->product_id) {
                $prod = Product::find($item->product_id);
                if ($prod) {
                    $cost = (float) ($prod->purchase_price ?? 0);
                    if ($prod->type === 'product') {
                        $prod->decrement('stock_quantity', $item->quantity);
                    }
                }
            }

            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'cost_price' => $cost,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }

        $quotation->update(['status' => 'converted']);

        ActivityLogger::log('convert_quotation', "Converted quotation {$quotation->quotation_number} to Invoice {$invoice->invoice_number}");

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Quotation converted to Invoice {$invoice->invoice_number} successfully!");
    }

    // ==========================================
    // --- INVOICES ---
    // ==========================================
    public function indexInvoices()
    {
        $invoices = Invoice::with(['customer', 'salesPerson'])->latest()->get();
        return view('billing.invoices.index', compact('invoices'));
    }

    public function createInvoice(Request $request)
    {
        $customers = Customer::with('invoices')->get();
        $products = Product::all();
        $company = Auth::user()->currentCompany;
        $teamUsers = $company ? $company->users()->get()->push($company->owner)->filter()->unique('id') : collect([Auth::user()]);
        $nextSeq = SequenceService::getNextNumber(Auth::user()->current_company_id, 'invoice', 'INV-');
        $selectedCustomerId = $request->get('customer_id');

        return view('billing.invoices.create', compact('customers', 'products', 'teamUsers', 'nextSeq', 'selectedCustomerId'));
    }

    public function showInvoice($id)
    {
        $invoice = Invoice::with(['customer', 'items.product', 'transactions.account', 'salesPerson'])->findOrFail($id);
        $accounts = Account::all();
        $company = Auth::user()->currentCompany;
        $whatsAppUrl = WhatsAppService::getInvoiceWhatsAppUrl($invoice);

        return view('billing.invoices.show', compact('invoice', 'accounts', 'company', 'whatsAppUrl'));
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'sales_person_id' => 'nullable|exists:users,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $companyId = Auth::user()->current_company_id;
        $seq = SequenceService::getNextNumber($companyId, 'invoice', 'INV-');

        $invoice = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $request->customer_id,
            'created_by_user_id' => Auth::id(),
            'sales_person_id' => $request->sales_person_id ?: null,
            'invoice_number' => $seq['formatted'],
            'sequence_number' => $seq['sequence_number'],
            'date' => $request->date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'status' => 'unpaid',
            'commission_rate' => $request->commission_rate ?? 0,
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $costPrice = isset($item['cost_price']) && is_numeric($item['cost_price']) ? (float)$item['cost_price'] : 0;
            if ($costPrice <= 0 && !empty($item['product_id'])) {
                $prodLookup = Product::find($item['product_id']);
                if ($prodLookup && $prodLookup->purchase_price > 0) {
                    $costPrice = (float) $prodLookup->purchase_price;
                }
            }

            $invoice->items()->create([
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'] ?? 'Item',
                'quantity' => $qty,
                'cost_price' => $costPrice,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ]);

            // Deduct stock if physical product
            if (!empty($item['product_id'])) {
                $prod = Product::find($item['product_id']);
                if ($prod && $prod->type === 'product') {
                    $prod->decrement('stock_quantity', $qty);
                }
            }
        }

        $discount = (float) ($request->discount ?? 0);
        $tax = (float) ($request->tax ?? 0);
        $grandTotal = max(0, ($subtotal - $discount) + $tax);

        $commissionRate = (float) ($request->commission_rate ?? 0);
        $commissionAmount = max(0, ($subtotal - $discount) * ($commissionRate / 100));

        $invoice->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $grandTotal,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
        ]);

        ActivityLogger::log('create_invoice', "Generated invoice {$invoice->invoice_number} for total {$grandTotal}");

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function editInvoice($id)
    {
        $invoice = Invoice::with(['items', 'customer', 'salesPerson'])->findOrFail($id);
        $customers = Customer::with('invoices')->get();
        $products = Product::all();
        $company = Auth::user()->currentCompany;
        $teamUsers = $company ? $company->users()->get()->push($company->owner)->filter()->unique('id') : collect([Auth::user()]);

        return view('billing.invoices.edit', compact('invoice', 'customers', 'products', 'teamUsers'));
    }

    public function updateInvoice(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'status' => 'required|in:unpaid,partially_paid,paid,cancelled',
            'sales_person_id' => 'nullable|exists:users,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = Invoice::findOrFail($id);

        $invoice->update([
            'customer_id' => $request->customer_id,
            'sales_person_id' => $request->sales_person_id ?: null,
            'date' => $request->date,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        // Clean & rebuild items
        $invoice->items()->delete();

        $subtotal = 0;
        foreach ($request->items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $costPrice = isset($item['cost_price']) && is_numeric($item['cost_price']) ? (float)$item['cost_price'] : 0;
            if ($costPrice <= 0 && !empty($item['product_id'])) {
                $prodLookup = Product::find($item['product_id']);
                if ($prodLookup && $prodLookup->purchase_price > 0) {
                    $costPrice = (float) $prodLookup->purchase_price;
                }
            }

            $invoice->items()->create([
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'] ?? 'Item',
                'quantity' => $qty,
                'cost_price' => $costPrice,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ]);
        }

        $discount = (float) ($request->discount ?? 0);
        $tax = (float) ($request->tax ?? 0);
        $grandTotal = max(0, ($subtotal - $discount) + $tax);

        $commissionRate = (float) ($request->commission_rate ?? 0);
        $commissionAmount = max(0, ($subtotal - $discount) * ($commissionRate / 100));

        $invoice->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $grandTotal,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
        ]);

        ActivityLogger::log('update_invoice', "Updated invoice {$invoice->invoice_number}");

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    public function destroyInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoiceNumber = $invoice->invoice_number;
        // Triggers boot model deleting event -> calls SequenceService::recycleNumber()
        $invoice->delete();

        ActivityLogger::log('delete_invoice', "Deleted invoice {$invoiceNumber} (sequence recycled)");

        return redirect()->route('invoices.index')->with('success', "Invoice {$invoiceNumber} deleted. Sequence number has been recycled for next invoice!");
    }

    // PDF Export
    public function pdfInvoice($id)
    {
        $invoice = Invoice::with(['customer', 'items.product'])->findOrFail($id);
        $company = Auth::user()->currentCompany;
        $pdf = Pdf::loadView('billing.invoices.pdf', compact('invoice', 'company'));
        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    // Email Invoice
    public function emailInvoice($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);
        ActivityLogger::log('email_invoice', "Emailed invoice {$invoice->invoice_number} to {$invoice->customer->email}");
        return redirect()->back()->with('success', "Invoice {$invoice->invoice_number} emailed to customer {$invoice->customer->email} successfully!");
    }

    // WhatsApp Dispatch
    public function whatsappInvoice($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);

        if (!$invoice->customer || !$invoice->customer->phone) {
            return redirect()->back()->with('error', "Customer does not have a phone number for WhatsApp dispatch.");
        }

        $waUrl = WhatsAppService::getInvoiceWhatsAppUrl($invoice);
        ActivityLogger::log('whatsapp_send', "Dispatched invoice {$invoice->invoice_number} via WhatsApp to {$invoice->customer->phone}");

        // Attempt automated API if cloud token is set
        $message = WhatsAppService::buildInvoiceMessage($invoice);
        $result = WhatsAppService::sendAutomated($invoice->customer->phone, $message);

        if ($result['success']) {
            return redirect()->back()->with('success', "Automated WhatsApp invoice successfully sent to {$invoice->customer->phone}!");
        }

        // Redirect to wa.me click-to-chat
        return redirect()->away($waUrl);
    }

    // Payment collection
    public function payInvoice(Request $request, $id)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $invoice = Invoice::findOrFail($id);
        $account = Account::findOrFail($request->account_id);

        $invoice->paid_amount += $request->amount;
        if ($invoice->paid_amount >= $invoice->total) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partially_paid';
        }
        $invoice->save();

        // Increment account balance
        $account->increment('current_balance', $request->amount);

        // Record income transaction
        Transaction::create([
            'company_id' => Auth::user()->current_company_id,
            'account_id' => $account->id,
            'invoice_id' => $invoice->id,
            'created_by_user_id' => Auth::id(),
            'type' => 'income',
            'amount' => $request->amount,
            'date' => now(),
            'description' => "Payment received for invoice {$invoice->invoice_number}",
        ]);

        ActivityLogger::log('invoice_payment', "Recorded payment of {$request->amount} for invoice {$invoice->invoice_number} into {$account->name}");

        return redirect()->back()->with('success', "Payment of {$request->amount} recorded for invoice {$invoice->invoice_number}.");
    }
}
