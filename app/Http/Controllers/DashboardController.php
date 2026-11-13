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

        // 1. Super Admin View (Global Platform Dashboard)
        if ($user->isSuperAdmin() && !$user->current_company_id) {
            $totalCompanies = Company::count();
            $totalUsers = User::count();
            $totalSystemInvoices = Invoice::count();
            $totalRevenue = Invoice::sum('total');
            $companies = Company::with('owner')->latest()->get();

            return view('dashboard.admin', compact(
                'totalCompanies', 'totalUsers', 'totalSystemInvoices', 'totalRevenue', 'companies'
            ));
        }

        // 2. Company-Scoped Workspace (Regular Admin OR Super Admin inspecting a company)
        $userCompanies = $user->isSuperAdmin() ? Company::all() : $user->companies;
        $companyId = $user->current_company_id;
        $company = $companyId ? Company::find($companyId) : null;

        // Auto-resolve first company if not set and user has companies
        if (!$company && $userCompanies->count() > 0) {
            $company = $userCompanies->first();
            $companyId = $company->id;
            $user->update(['current_company_id' => $companyId]);
        }

        $currency = $company ? ($company->currency ?: 'PKR') : 'PKR';

        // Tenancy Scoped KPIs (Strictly for Active Company)
        $totalLeads = $companyId ? Lead::where('company_id', $companyId)->count() : 0;
        $totalCustomers = $companyId ? Customer::where('company_id', $companyId)->count() : 0;
        $totalInvoices = $companyId ? Invoice::where('company_id', $companyId)->count() : 0;
        $totalProducts = $companyId ? Product::where('company_id', $companyId)->count() : 0;
        $totalPurchases = $companyId ? Purchase::where('company_id', $companyId)->count() : 0;

        // Financial KPIs (GAAP Sequence: Revenue -> COGS -> Gross Profit -> OPEX -> Operating Profit -> Net Profit)
        $totalRevenue = $companyId ? Transaction::where('company_id', $companyId)->where('type', 'income')->sum('amount') : 0;
        if ($totalRevenue == 0 && $companyId) {
            $totalRevenue = Invoice::where('company_id', $companyId)->where('status', 'paid')->sum('total');
        }

        $cogs = $companyId ? Purchase::where('company_id', $companyId)->sum('total') : 0;
        $grossProfit = $totalRevenue - $cogs;
        $operatingExpenses = $companyId ? Expense::where('company_id', $companyId)->sum('amount') : 0;
        $operatingProfit = $grossProfit - $operatingExpenses;
        $netProfit = $operatingProfit;

        $totalIncome = $totalRevenue;
        $totalExpenses = $operatingExpenses;

        $recentInvoices = $companyId ? Invoice::where('company_id', $companyId)->with('customer')->latest()->take(6)->get() : collect();
        $recentLeads = $companyId ? Lead::where('company_id', $companyId)->latest()->take(6)->get() : collect();

        // Platform Stats for Super Admin banner if inspecting
        $isSuperAdmin = $user->isSuperAdmin();
        $totalCompanies = Company::count();
        $totalUsers = User::count();

        return view('dashboard.company', compact(
            'company', 'userCompanies', 'currency', 'totalLeads', 'totalCustomers', 
            'totalInvoices', 'totalProducts', 'totalPurchases', 'totalIncome', 'totalExpenses', 
            'totalRevenue', 'cogs', 'grossProfit', 'operatingExpenses', 'operatingProfit',
            'netProfit', 'recentInvoices', 'recentLeads', 'isSuperAdmin', 'totalCompanies', 'totalUsers'
        ));
    }
}
