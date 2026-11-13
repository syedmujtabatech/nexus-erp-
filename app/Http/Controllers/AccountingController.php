<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InvoiceItem;
use App\Services\ActivityLogger;
use App\Services\SequenceService;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountingController extends Controller
{
    // ==========================================
    // --- VENDORS ---
    // ==========================================
    public function indexVendors()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $vendors = Vendor::where('company_id', $companyId)->withCount(['expenses', 'purchases'])->latest()->get();
        return view('accounting.vendors.index', compact('vendors'));
    }

    public function storeVendor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vendor = Vendor::create([
            'company_id' => Auth::user()->current_company_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        ActivityLogger::log('create_vendor', "Added vendor: {$vendor->name}");

        return redirect()->back()->with('success', 'Vendor registered successfully.');
    }

    public function updateVendor(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        ActivityLogger::log('update_vendor', "Updated vendor: {$vendor->name}");

        return redirect()->back()->with('success', 'Vendor updated successfully.');
    }

    public function destroyVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $name = $vendor->name;
        $vendor->delete();

        ActivityLogger::log('delete_vendor', "Deleted vendor: {$name}");

        return redirect()->route('vendors.index')->with('success', "Vendor '{$name}' deleted successfully.");
    }

    // ==========================================
    // --- PURCHASES (Supplier Bills & Stock Inward) ---
    // ==========================================
    public function indexPurchases(Request $request)
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $query = Purchase::where('company_id', $companyId)->with(['vendor', 'createdBy'])->latest();

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $purchases = $query->paginate(20);
        $vendors = Vendor::where('company_id', $companyId)->get();
        $accounts = Account::where('company_id', $companyId)->get();

        $totalPurchases = Purchase::where('company_id', $companyId)->sum('total');
        $totalPaid = Purchase::where('company_id', $companyId)->sum('paid_amount');
        $totalOutstanding = $totalPurchases - $totalPaid;

        return view('accounting.purchases.index', compact(
            'purchases', 'vendors', 'accounts', 'totalPurchases', 'totalPaid', 'totalOutstanding'
        ));
    }

    public function createPurchase()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $vendors = Vendor::where('company_id', $companyId)->get();
        $products = Product::where('company_id', $companyId)->get();
        $accounts = Account::where('company_id', $companyId)->get();
        return view('accounting.purchases.create', compact('vendors', 'products', 'accounts'));
    }

    public function storePurchase(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->current_company_id;
        $seq = SequenceService::getNextNumber($companyId, 'purchase', 'BILL-');

        $purchase = Purchase::create([
            'company_id' => $companyId,
            'vendor_id' => $request->vendor_id,
            'created_by_user_id' => Auth::id(),
            'purchase_number' => $seq['formatted'],
            'sequence_number' => $seq['sequence_number'],
            'date' => $request->date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'status' => 'unpaid',
            'paid_amount' => 0,
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $purchase->items()->create([
                'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                'description' => $item['description'] ?? 'Item',
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $totalPrice,
            ]);

            // AUTO-INCREMENT PRODUCT STOCK IN WAREHOUSE
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('stock_quantity', $qty);
                    // Update purchase price if higher/updated
                    if ($price > 0) {
                        $product->update(['purchase_price' => $price]);
                    }
                }
            }
        }

        $discount = (float) ($request->discount ?? 0);
        $tax = (float) ($request->tax ?? 0);
        $grandTotal = max(0, ($subtotal - $discount) + $tax);

        $purchase->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $grandTotal,
        ]);

        // If initial payment was made simultaneously
        if ($request->filled('initial_payment') && (float)$request->initial_payment > 0 && $request->filled('payment_account_id')) {
            $paymentAmount = min($grandTotal, (float)$request->initial_payment);
            $account = Account::findOrFail($request->payment_account_id);

            if ($account->current_balance >= $paymentAmount) {
                $account->decrement('current_balance', $paymentAmount);

                $purchase->update([
                    'paid_amount' => $paymentAmount,
                    'status' => $paymentAmount >= $grandTotal ? 'paid' : 'partially_paid',
                ]);

                Transaction::create([
                    'company_id' => $companyId,
                    'account_id' => $account->id,
                    'purchase_id' => $purchase->id,
                    'vendor_id' => $purchase->vendor_id,
                    'created_by_user_id' => Auth::id(),
                    'type' => 'expense',
                    'amount' => $paymentAmount,
                    'date' => $request->date,
                    'description' => "Initial payment for Purchase Bill {$purchase->purchase_number}",
                ]);
            }
        }

        ActivityLogger::log('create_purchase', "Created purchase bill {$purchase->purchase_number} for {$grandTotal}");

        return redirect()->route('purchases.show', $purchase->id)->with('success', "Purchase Bill {$purchase->purchase_number} created and inventory stock updated!");
    }

    public function showPurchase($id)
    {
        $purchase = Purchase::with(['vendor', 'items.product', 'transactions.account', 'createdBy'])->findOrFail($id);
        $accounts = Account::all();
        return view('accounting.purchases.show', compact('purchase', 'accounts'));
    }

    public function destroyPurchase($id)
    {
        $purchase = Purchase::findOrFail($id);
        $billNo = $purchase->purchase_number;
        $purchase->delete(); // Automatically decrements stock & recycles sequence number

        ActivityLogger::log('delete_purchase', "Deleted purchase bill {$billNo} and reversed inventory stock");

        return redirect()->route('purchases.index')->with('success', "Purchase bill {$billNo} deleted. Stock adjusted and sequence recycled!");
    }

    public function payPurchase(Request $request, $id)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $purchase = Purchase::findOrFail($id);
        $account = Account::findOrFail($request->account_id);

        $due = $purchase->total - $purchase->paid_amount;
        if ($due <= 0) {
            return redirect()->back()->with('error', 'This purchase bill is already fully paid.');
        }

        if ($request->amount > $due) {
            return redirect()->back()->with('error', "Payment cannot exceed remaining due amount of {$due}.");
        }

        if ($account->current_balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient funds in the selected bank/cash account.');
        }

        $account->decrement('current_balance', $request->amount);

        $newPaid = $purchase->paid_amount + $request->amount;
        $newStatus = ($newPaid >= $purchase->total) ? 'paid' : 'partially_paid';

        $purchase->update([
            'paid_amount' => $newPaid,
            'status' => $newStatus,
        ]);

        Transaction::create([
            'company_id' => Auth::user()->current_company_id,
            'account_id' => $account->id,
            'purchase_id' => $purchase->id,
            'vendor_id' => $purchase->vendor_id,
            'created_by_user_id' => Auth::id(),
            'type' => 'expense',
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => "Supplier Payment for Bill #{$purchase->purchase_number}: " . ($request->notes ?? ''),
        ]);

        ActivityLogger::log('pay_purchase', "Paid {$request->amount} for purchase {$purchase->purchase_number} from {$account->name}");

        return redirect()->back()->with('success', "Payment of {$request->amount} recorded successfully.");
    }

    // ==========================================
    // --- SUPPLIER STATEMENT / LEDGER ---
    // ==========================================
    public function indexSupplierLedger(Request $request)
    {
        $vendors = Vendor::all();
        $selectedVendor = null;
        $purchases = collect();
        $transactions = collect();
        $totalBilled = 0;
        $totalPaid = 0;
        $balanceDue = 0;

        if ($request->filled('vendor_id')) {
            $selectedVendor = Vendor::findOrFail($request->vendor_id);
            $queryPurchases = Purchase::where('vendor_id', $selectedVendor->id);
            $queryTransactions = Transaction::where('vendor_id', $selectedVendor->id)->where('type', 'expense');

            if ($request->filled('from_date')) {
                $queryPurchases->whereDate('date', '>=', $request->from_date);
                $queryTransactions->whereDate('date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $queryPurchases->whereDate('date', '<=', $request->to_date);
                $queryTransactions->whereDate('date', '<=', $request->to_date);
            }

            $purchases = $queryPurchases->latest()->get();
            $transactions = $queryTransactions->latest()->get();

            $totalBilled = $purchases->sum('total');
            $totalPaid = $transactions->sum('amount');
            $balanceDue = max(0, $totalBilled - $totalPaid);
        }

        return view('accounting.supplier_ledger.index', compact(
            'vendors', 'selectedVendor', 'purchases', 'transactions', 'totalBilled', 'totalPaid', 'balanceDue'
        ));
    }

    // ==========================================
    // --- ACCOUNTS (Bank & Cash) ---
    // ==========================================
    public function indexAccounts()
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $accounts = Account::where('company_id', $companyId)->withCount('transactions')->latest()->get();
        $totalLiquidity = $accounts->sum('current_balance');
        return view('accounting.accounts.index', compact('accounts', 'totalLiquidity'));
    }

    public function showAccount($id)
    {
        $account = Account::findOrFail($id);
        $transactions = Transaction::where(function($q) use ($account) {
            $q->where('account_id', $account->id)->orWhere('to_account_id', $account->id);
        })->latest()->paginate(25);

        return view('accounting.accounts.show', compact('account', 'transactions'));
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);

        $opening = $request->opening_balance ?? 0;

        $account = Account::create([
            'company_id' => Auth::user()->current_company_id,
            'name' => $request->name,
            'account_number' => $request->account_number,
            'opening_balance' => $opening,
            'current_balance' => $opening,
        ]);

        ActivityLogger::log('create_account', "Created account: {$account->name} with balance {$account->opening_balance}");

        return redirect()->back()->with('success', 'Account created successfully.');
    }

    public function updateAccount(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $account = Account::findOrFail($id);
        $account->update([
            'name' => $request->name,
            'account_number' => $request->account_number,
        ]);

        ActivityLogger::log('update_account', "Updated account: {$account->name}");

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function destroyAccount($id)
    {
        $account = Account::findOrFail($id);
        $name = $account->name;

        $hasTx = Transaction::where('account_id', $account->id)->orWhere('to_account_id', $account->id)->exists();
        if ($hasTx) {
            return redirect()->back()->with('error', "Cannot delete account '{$name}' with existing transaction history.");
        }

        $account->delete();
        ActivityLogger::log('delete_account', "Deleted account: {$name}");

        return redirect()->route('accounts.index')->with('success', "Account '{$name}' deleted successfully.");
    }

    public function internalTransfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $fromAccount = Account::findOrFail($request->from_account_id);
        $toAccount = Account::findOrFail($request->to_account_id);

        if ($fromAccount->current_balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient funds in the source account.');
        }

        $fromAccount->decrement('current_balance', $request->amount);
        $toAccount->increment('current_balance', $request->amount);

        Transaction::create([
            'company_id' => Auth::user()->current_company_id,
            'account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'created_by_user_id' => Auth::id(),
            'type' => 'internal_transfer',
            'amount' => $request->amount,
            'date' => now(),
            'description' => "Internal transfer from {$fromAccount->name} to {$toAccount->name}",
        ]);

        ActivityLogger::log('internal_transfer', "Transferred {$request->amount} from {$fromAccount->name} to {$toAccount->name}");

        return redirect()->back()->with('success', 'Internal transfer completed successfully.');
    }

    // ==========================================
    // --- EXPENSES (With Advanced Filters & Reports) ---
    // ==========================================
    public function indexExpenses(Request $request)
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $query = Expense::where('company_id', $companyId)->with(['vendor', 'account'])->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $expenses = $query->paginate(20)->withQueryString();

        // Calculate summary metrics for current filter
        $metricQuery = clone $query;
        $totalExpenseAmount = $metricQuery->sum('amount');
        $expenseCount = $expenses->total();
        $averageExpense = $expenseCount > 0 ? ($totalExpenseAmount / $expenseCount) : 0;

        $categories = Expense::where('company_id', $companyId)->select('category')->distinct()->pluck('category');
        $vendors = Vendor::where('company_id', $companyId)->get();
        $accounts = Account::where('company_id', $companyId)->get();

        return view('accounting.expenses.index', compact(
            'expenses', 'vendors', 'accounts', 'categories', 'totalExpenseAmount', 'expenseCount', 'averageExpense'
        ));
    }

    public function storeExpense(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
        ]);

        $account = Account::findOrFail($request->account_id);
        if ($account->current_balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient balance in selected account for this expense.');
        }

        $expense = Expense::create([
            'company_id' => Auth::user()->current_company_id,
            'vendor_id' => $request->vendor_id,
            'account_id' => $request->account_id,
            'created_by_user_id' => Auth::id(),
            'category' => $request->category,
            'amount' => $request->amount,
            'date' => $request->date,
            'reference' => $request->reference,
            'description' => $request->description,
        ]);

        $account->decrement('current_balance', $request->amount);

        Transaction::create([
            'company_id' => Auth::user()->current_company_id,
            'account_id' => $account->id,
            'expense_id' => $expense->id,
            'vendor_id' => $request->vendor_id,
            'created_by_user_id' => Auth::id(),
            'type' => 'expense',
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => "Expense: {$request->category} - " . ($request->description ?? ''),
        ]);

        ActivityLogger::log('create_expense', "Logged expense: {$expense->category} for {$expense->amount} from {$account->name}");

        return redirect()->back()->with('success', 'Expense logged and account updated successfully.');
    }

    public function updateExpense(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string',
            'date' => 'required|date',
        ]);

        $expense = Expense::findOrFail($id);
        $expense->update([
            'category' => $request->category,
            'vendor_id' => $request->vendor_id,
            'date' => $request->date,
            'reference' => $request->reference,
            'description' => $request->description,
        ]);

        ActivityLogger::log('update_expense', "Updated expense: {$expense->category} (ID: {$expense->id})");

        return redirect()->back()->with('success', 'Expense details updated successfully.');
    }

    public function destroyExpense($id)
    {
        $expense = Expense::findOrFail($id);
        $amount = $expense->amount;
        $category = $expense->category;

        if ($expense->account) {
            $expense->account->increment('current_balance', $amount);
        }

        Transaction::where('expense_id', $expense->id)->delete();
        $expense->delete();

        ActivityLogger::log('delete_expense', "Deleted expense: {$category} ({$amount}) and refunded account");

        return redirect()->route('expenses.index')->with('success', "Expense deleted and account refunded.");
    }

    // ==========================================
    // --- CUSTOMER LEDGER STATEMENT ---
    // ==========================================
    public function indexLedger(Request $request)
    {
        $companyId = Auth::user()->current_company_id ?? 5;
        $customers = Customer::where('company_id', $companyId)->get();
        $selectedCustomer = null;
        $invoices = collect();
        $transactions = collect();
        $totalBilled = 0;
        $totalPaid = 0;
        $balanceDue = 0;

        if ($request->has('customer_id') && $request->customer_id) {
            $selectedCustomer = Customer::where('company_id', $companyId)->findOrFail($request->customer_id);
            $invoices = Invoice::where('company_id', $companyId)->where('customer_id', $selectedCustomer->id)->latest()->get();
            $invoiceIds = $invoices->pluck('id');
            $transactions = Transaction::where('company_id', $companyId)->whereIn('invoice_id', $invoiceIds)->where('type', 'income')->latest()->get();

            $totalBilled = $invoices->sum('total');
            $totalPaid = $invoices->sum('paid_amount');
            $balanceDue = $totalBilled - $totalPaid;
        }

        return view('accounting.ledger.index', compact(
            'customers', 'selectedCustomer', 'invoices', 'transactions', 'totalBilled', 'totalPaid', 'balanceDue'
        ));
    }

    // ==========================================
    // --- PROFIT & LOSS REPORT ---
    // ==========================================
    public function reportProfitLoss()
    {
        $company = Auth::user()->currentCompany;
        $companyId = $company ? $company->id : 5;
        $currency = $company ? ($company->currency ?: 'PKR') : 'PKR';

        // 1. Total Revenue (Sales Income & Receipts)
        $totalRevenue = Transaction::where('company_id', $companyId)->where('type', 'income')->sum('amount');
        if ($totalRevenue == 0) {
            $totalRevenue = Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('total');
        }

        // 2. Cost of Goods Sold (COGS)
        $cogs = 0;
        $invoiceItems = InvoiceItem::whereHasMorph('itemable', [Invoice::class], function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with('product')->get();

        foreach ($invoiceItems as $item) {
            if ($item->product && $item->product->purchase_price) {
                $cogs += ($item->product->purchase_price * $item->quantity);
            }
        }
        if ($cogs == 0) {
            $cogs = Purchase::where('company_id', $companyId)->sum('total');
        }

        // 3. Gross Profit = Total Revenue - COGS
        $grossProfit = $totalRevenue - $cogs;

        // 4. Operating Expenses (OPEX: Rent, Utilities, Salaries, Overhead)
        $operatingExpenses = Expense::where('company_id', $companyId)->sum('amount');

        // 5. Operating Profit (EBIT) = Gross Profit - Operating Expenses
        $operatingProfit = $grossProfit - $operatingExpenses;

        // 6. Net Profit (Final Bottom-Line Margin)
        $netProfit = $operatingProfit;

        // Backwards compatibility variable aliases
        $totalSalesIncome = $totalRevenue;
        $totalExpenses = $operatingExpenses;

        return view('accounting.reports.profit_loss', compact(
            'currency', 'totalRevenue', 'cogs', 'grossProfit', 'operatingExpenses', 'operatingProfit', 'netProfit',
            'totalSalesIncome', 'totalExpenses'
        ));
    }

    public function pdfProfitLoss()
    {
        $company = Auth::user()->currentCompany;
        $companyId = $company ? $company->id : 5;
        $currency = $company ? ($company->currency ?: 'PKR') : 'PKR';

        // 1. Total Revenue
        $totalRevenue = Transaction::where('company_id', $companyId)->where('type', 'income')->sum('amount');
        if ($totalRevenue == 0) {
            $totalRevenue = Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('total');
        }

        // 2. Cost of Goods Sold (COGS)
        $cogs = 0;
        $invoiceItems = InvoiceItem::whereHasMorph('itemable', [Invoice::class], function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with('product')->get();

        foreach ($invoiceItems as $item) {
            if ($item->product && $item->product->purchase_price) {
                $cogs += ($item->product->purchase_price * $item->quantity);
            }
        }
        if ($cogs == 0) {
            $cogs = Purchase::where('company_id', $companyId)->sum('total');
        }

        // 3. Gross Profit
        $grossProfit = $totalRevenue - $cogs;

        // 4. Operating Expenses
        $operatingExpenses = Expense::where('company_id', $companyId)->sum('amount');

        // 5. Operating Profit
        $operatingProfit = $grossProfit - $operatingExpenses;

        // 6. Net Profit
        $netProfit = $operatingProfit;

        $totalSalesIncome = $totalRevenue;
        $totalExpenses = $operatingExpenses;

        $pdf = Pdf::loadView('accounting.reports.pdf_profit_loss', compact(
            'company', 'currency', 'totalRevenue', 'cogs', 'grossProfit', 'operatingExpenses', 'operatingProfit', 'netProfit',
            'totalSalesIncome', 'totalExpenses'
        ));

        return $pdf->download("Profit_Loss_Report_" . now()->format('Y-m-d') . ".pdf");
    }
}
