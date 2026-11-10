<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Resolve Active Company Workspace
        $companyId = $user->current_company_id;
        $company = $companyId ? Company::find($companyId) : null;
        $userCompanies = $user->companies;

        if (!$company && $userCompanies->count() > 0) {
            $company = $userCompanies->first();
            $companyId = $company->id;
            $user->update(['current_company_id' => $companyId]);
        }

        if (!$company) {
            $company = Company::first();
            if ($company) {
                $companyId = $company->id;
                $user->update(['current_company_id' => $companyId]);
            }
        }

        $currency = $company ? ($company->currency ?: 'PKR') : 'PKR';

        // 2. Tenancy Scoped KPIs (Strictly for Active Company)
        $totalLeads = Lead::where('company_id', $companyId)->count();
        $totalCustomers = Customer::where('company_id', $companyId)->count();
        $totalInvoices = Invoice::where('company_id', $companyId)->count();
        $totalProducts = Product::where('company_id', $companyId)->count();
        $totalPurchases = Purchase::where('company_id', $companyId)->count();

        // Financial KPIs for active company
        $totalIncome = Transaction::where('company_id', $companyId)->where('type', 'income')->sum('amount');
        $totalExpenses = Expense::where('company_id', $companyId)->sum('amount');
        $netProfit = $totalIncome - $totalExpenses;

        $recentInvoices = Invoice::where('company_id', $companyId)->with('customer')->latest()->take(6)->get();
        $recentLeads = Lead::where('company_id', $companyId)->latest()->take(6)->get();

        // Global Platform Stats (for Super Admin summary card)
        $isSuperAdmin = $user->isSuperAdmin();
        $totalCompanies = Company::count();
        $totalUsers = User::count();

        return view('dashboard.company', compact(
            'company', 'userCompanies', 'currency', 'totalLeads', 'totalCustomers', 
            'totalInvoices', 'totalProducts', 'totalPurchases', 'totalIncome', 'totalExpenses', 
            'netProfit', 'recentInvoices', 'recentLeads', 'isSuperAdmin', 'totalCompanies', 'totalUsers'
        ));
    }
}
