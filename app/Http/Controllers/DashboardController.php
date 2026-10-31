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

        // 1. Super Admin View (Role 1)
        if ($user->isSuperAdmin()) {
            $totalCompanies = Company::count();
            $totalUsers = User::count();
            $totalSystemInvoices = Invoice::count();
            $totalRevenue = Transaction::where('type', 'income')->sum('amount');
            $companies = Company::with('owner')->latest()->take(10)->get();

            return view('dashboard.admin', compact('totalCompanies', 'totalUsers', 'totalSystemInvoices', 'totalRevenue', 'companies'));
        }

        // 2. Company Owner / Subuser View (Role 2 & 3)
        $company = $user->currentCompany;
        $userCompanies = $user->companies;

        if (!$company && $userCompanies->count() > 0) {
            $company = $userCompanies->first();
            $user->update(['current_company_id' => $company->id]);
        }

        $currency = $company ? $company->currency : 'USD';

        $totalLeads = Lead::count();
        $totalCustomers = Customer::count();
        $totalInvoices = Invoice::count();
        $totalProducts = Product::count();
        $totalPurchases = Purchase::count();

        // Financial KPIs
        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpenses = Expense::sum('amount');
        $netProfit = $totalIncome - $totalExpenses;

        $recentInvoices = Invoice::with('customer')->latest()->take(5)->get();
        $recentLeads = Lead::latest()->take(5)->get();

        return view('dashboard.company', compact(
            'company', 'userCompanies', 'currency', 'totalLeads', 'totalCustomers', 
            'totalInvoices', 'totalProducts', 'totalPurchases', 'totalIncome', 'totalExpenses', 
            'netProfit', 'recentInvoices', 'recentLeads'
        ));
    }
}
