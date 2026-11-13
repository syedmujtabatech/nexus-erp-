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
        $totalQuotations = $companyId ? Quotation::where('company_id', $companyId)->count() : 0;

        // CRM Pipeline & Conversion Metrics
        $pipelineValue = $companyId ? Lead::where('company_id', $companyId)->sum('value') : 0;
        $convertedLeads = $companyId ? Lead::where('company_id', $companyId)->where('status', 'converted')->count() : 0;
        $lostLeads = $companyId ? Lead::where('company_id', $companyId)->where('status', 'lost')->count() : 0;
        $activeLeads = max(0, $totalLeads - $convertedLeads - $lostLeads);
        $winRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Lead Stages Funnel Distribution
        $stageNew = $companyId ? Lead::where('company_id', $companyId)->where('status', 'new')->count() : 0;
        $stageContacted = $companyId ? Lead::where('company_id', $companyId)->where('status', 'contacted')->count() : 0;
        $stageQualified = $companyId ? Lead::where('company_id', $companyId)->where('status', 'qualified')->count() : 0;
        $stageProposal = $companyId ? Lead::where('company_id', $companyId)->where('status', 'proposal_sent')->count() : 0;
        $stageWon = $convertedLeads;
        $stageLost = $lostLeads;
        $funnelData = [$stageNew, $stageContacted, $stageQualified, $stageProposal, $stageWon, $stageLost];

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

        // Average Deal / Invoice Value
        $avgDealValue = $totalInvoices > 0 ? round($totalRevenue / $totalInvoices, 2) : 0;

        // 6-Month Rolling Financial Trend (For ApexCharts Area/Spline)
        $monthLabels = [];
        $monthlyRevenueSeries = [];
        $monthlyExpenseSeries = [];
        $monthlyProfitSeries = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabels[] = $date->format('M');
            $year = $date->year;
            $month = $date->month;

            $mRev = $companyId ? (
                Transaction::where('company_id', $companyId)
                    ->where('type', 'income')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount')
            ) : 0;

            if ($mRev == 0 && $companyId) {
                $mRev = Invoice::where('company_id', $companyId)
                    ->where('status', 'paid')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('total');
            }

            $mExp = $companyId ? (
                Expense::where('company_id', $companyId)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->sum('amount')
            ) : 0;

            // If current month and recorded data has no date or falls under overall totals
            if ($i === 0 && $mRev == 0 && $totalRevenue > 0) {
                $mRev = $totalRevenue;
            }
            if ($i === 0 && $mExp == 0 && $operatingExpenses > 0) {
                $mExp = $operatingExpenses;
            }

            $mProfit = $mRev - $mExp;

            $monthlyRevenueSeries[] = (float) $mRev;
            $monthlyExpenseSeries[] = (float) $mExp;
            $monthlyProfitSeries[] = (float) $mProfit;
        }

        // Target Quota Progress (Default target e.g. 500,000 or 2x current revenue)
        $monthlyTarget = max(500000, $totalRevenue * 1.5);
        $quotaPercentage = $monthlyTarget > 0 ? min(100, round(($totalRevenue / $monthlyTarget) * 100)) : 0;

        $recentInvoices = $companyId ? Invoice::where('company_id', $companyId)->with('customer')->latest()->take(6)->get() : collect();
        $recentLeads = $companyId ? Lead::where('company_id', $companyId)->latest()->take(6)->get() : collect();

        // Platform Stats for Super Admin banner if inspecting
        $isSuperAdmin = $user->isSuperAdmin();
        $totalCompanies = Company::count();
        $totalUsers = User::count();

        return view('dashboard.company', compact(
            'company', 'userCompanies', 'currency', 'totalLeads', 'totalCustomers', 
            'totalInvoices', 'totalProducts', 'totalPurchases', 'totalQuotations', 'totalIncome', 'totalExpenses', 
            'totalRevenue', 'cogs', 'grossProfit', 'operatingExpenses', 'operatingProfit',
            'netProfit', 'recentInvoices', 'recentLeads', 'isSuperAdmin', 'totalCompanies', 'totalUsers',
            'pipelineValue', 'convertedLeads', 'lostLeads', 'activeLeads', 'winRate', 'avgDealValue',
            'stageNew', 'stageContacted', 'stageQualified', 'stageProposal', 'stageWon', 'stageLost', 'funnelData',
            'monthLabels', 'monthlyRevenueSeries', 'monthlyExpenseSeries', 'monthlyProfitSeries',
            'monthlyTarget', 'quotaPercentage'
        ));
    }
}
