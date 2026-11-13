<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CRMController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;

// Auth Routes
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('oauth.google');
Route::match(['get', 'post'], '/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('oauth.google.callback');

// OTP Verification Screen (Accessible to unverified auth users)
Route::middleware('auth')->group(function () {
    Route::get('/verify-otp', [AuthController::class, 'showOtpVerify'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/company/switch', [AuthController::class, 'switchCompany'])->name('company.switch');
    Route::get('/company/switch-all', [AuthController::class, 'switchAll'])->name('company.switch.all');
    Route::post('/company/dismiss-modal', [CompanyController::class, 'dismissModal'])->name('company.dismiss_modal');
});

// Protected Workspace & CRM Routes (STRICTLY Protected by `otp.verified` middleware)
Route::middleware(['auth', 'otp.verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile & Account Settings
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Super Admin Management Routes
    Route::get('/admin/companies', [AdminController::class, 'companies'])->name('admin.companies');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/admin/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Companies Management & SaaS Subscription
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::post('/companies/{id}/plan', [CompanyController::class, 'updatePlan'])->name('companies.plan');
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    // Activity & Login Audit Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.index');

    // CRM Modules - Customers
    Route::get('/customers', [CRMController::class, 'indexCustomers'])->name('customers.index');
    Route::post('/customers', [CRMController::class, 'storeCustomer'])->name('customers.store');
    Route::get('/customers/{id}', [CRMController::class, 'showCustomer'])->name('customers.show');
    Route::post('/customers/{id}', [CRMController::class, 'updateCustomer'])->name('customers.update');
    Route::delete('/customers/{id}', [CRMController::class, 'destroyCustomer'])->name('customers.destroy');

    // CRM Modules - Leads
    Route::get('/leads', [CRMController::class, 'indexLeads'])->name('leads.index');
    Route::post('/leads', [CRMController::class, 'storeLead'])->name('leads.store');
    Route::get('/leads/{id}', [CRMController::class, 'showLead'])->name('leads.show');
    Route::post('/leads/{id}', [CRMController::class, 'updateLead'])->name('leads.update');
    Route::delete('/leads/{id}', [CRMController::class, 'destroyLead'])->name('leads.destroy');
    Route::post('/leads/{id}/convert', [CRMController::class, 'convertLead'])->name('leads.convert');

    // Products & Services
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Billing & Invoicing - Quotations
    Route::get('/quotations', [BillingController::class, 'indexQuotations'])->name('quotations.index');
    Route::get('/quotations/create', [BillingController::class, 'createQuotation'])->name('quotations.create');
    Route::post('/quotations', [BillingController::class, 'storeQuotation'])->name('quotations.store');
    Route::get('/quotations/{id}', [BillingController::class, 'showQuotation'])->name('quotations.show');
    Route::get('/quotations/{id}/edit', [BillingController::class, 'editQuotation'])->name('quotations.edit');
    Route::post('/quotations/{id}', [BillingController::class, 'updateQuotation'])->name('quotations.update');
    Route::delete('/quotations/{id}', [BillingController::class, 'destroyQuotation'])->name('quotations.destroy');
    Route::post('/quotations/{id}/convert', [BillingController::class, 'convertQuotationToInvoice'])->name('quotations.convert');

    // Billing & Invoicing - Invoices
    Route::get('/invoices', [BillingController::class, 'indexInvoices'])->name('invoices.index');
    Route::get('/invoices/create', [BillingController::class, 'createInvoice'])->name('invoices.create');
    Route::post('/invoices', [BillingController::class, 'storeInvoice'])->name('invoices.store');
    Route::get('/invoices/{id}', [BillingController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{id}/edit', [BillingController::class, 'editInvoice'])->name('invoices.edit');
    Route::post('/invoices/{id}', [BillingController::class, 'updateInvoice'])->name('invoices.update');
    Route::delete('/invoices/{id}', [BillingController::class, 'destroyInvoice'])->name('invoices.destroy');
    Route::get('/invoices/{id}/pdf', [BillingController::class, 'pdfInvoice'])->name('invoices.pdf');
    Route::get('/invoices/{id}/email', [BillingController::class, 'emailInvoice'])->name('invoices.email');
    Route::get('/invoices/{id}/whatsapp', [BillingController::class, 'whatsappInvoice'])->name('invoices.whatsapp');
    Route::post('/invoices/{id}/pay', [BillingController::class, 'payInvoice'])->name('invoices.pay');

    // Procurement / Purchases (Supplier Bills & Stock Inward)
    Route::get('/purchases', [AccountingController::class, 'indexPurchases'])->name('purchases.index');
    Route::get('/purchases/create', [AccountingController::class, 'createPurchase'])->name('purchases.create');
    Route::post('/purchases', [AccountingController::class, 'storePurchase'])->name('purchases.store');
    Route::get('/purchases/{id}', [AccountingController::class, 'showPurchase'])->name('purchases.show');
    Route::delete('/purchases/{id}', [AccountingController::class, 'destroyPurchase'])->name('purchases.destroy');
    Route::post('/purchases/{id}/pay', [AccountingController::class, 'payPurchase'])->name('purchases.pay');

    // Accounting & Reports - Vendors
    Route::get('/vendors', [AccountingController::class, 'indexVendors'])->name('vendors.index');
    Route::post('/vendors', [AccountingController::class, 'storeVendor'])->name('vendors.store');
    Route::post('/vendors/{id}', [AccountingController::class, 'updateVendor'])->name('vendors.update');
    Route::delete('/vendors/{id}', [AccountingController::class, 'destroyVendor'])->name('vendors.destroy');

    // Accounting & Reports - Accounts (Bank & Cash)
    Route::get('/accounts', [AccountingController::class, 'indexAccounts'])->name('accounts.index');
    Route::post('/accounts', [AccountingController::class, 'storeAccount'])->name('accounts.store');
    Route::get('/accounts/{id}', [AccountingController::class, 'showAccount'])->name('accounts.show');
    Route::post('/accounts/{id}', [AccountingController::class, 'updateAccount'])->name('accounts.update');
    Route::delete('/accounts/{id}', [AccountingController::class, 'destroyAccount'])->name('accounts.destroy');
    Route::post('/accounts/transfer', [AccountingController::class, 'internalTransfer'])->name('accounts.transfer');

    // Accounting & Reports - Expenses
    Route::get('/expenses', [AccountingController::class, 'indexExpenses'])->name('expenses.index');
    Route::post('/expenses', [AccountingController::class, 'storeExpense'])->name('expenses.store');
    Route::post('/expenses/{id}', [AccountingController::class, 'updateExpense'])->name('expenses.update');
    Route::delete('/expenses/{id}', [AccountingController::class, 'destroyExpense'])->name('expenses.destroy');

    // Accounting & Reports - Ledgers & Reports
    Route::get('/ledger', [AccountingController::class, 'indexLedger'])->name('ledger.index');
    Route::get('/supplier-ledger', [AccountingController::class, 'indexSupplierLedger'])->name('supplier_ledger.index');
    Route::get('/reports/profit-loss', [AccountingController::class, 'reportProfitLoss'])->name('reports.profit_loss');
    Route::get('/reports/profit-loss/pdf', [AccountingController::class, 'pdfProfitLoss'])->name('reports.profit_loss.pdf');

    // Team Members
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    Route::post('/team', [TeamController::class, 'store'])->name('team.store');
    Route::post('/team/{id}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{id}', [TeamController::class, 'destroy'])->name('team.destroy');
});
