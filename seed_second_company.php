<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "Seeding Company 14 (Al-Rahim Logistics & Trading) with separate tenant data...\n";

// 1. Ensure admin accounts
$adminCorporate = User::firstOrNew(['email' => 'admin@nexus-erp.com']);
$adminCorporate->name = 'Nexus System Admin';
$adminCorporate->password = Hash::make('Nexus@2026');
$adminCorporate->role_id = 1;
$adminCorporate->is_otp_verified = 1;
$adminCorporate->phone = '+92 300 0000000';
$adminCorporate->save();

$user1 = User::where('email', 'syedmujtaba.dev@gmail.com')->first();
if ($user1) {
    $user1->password = Hash::make('Nexus@2026');
    $user1->save();
}

// 2. Company 14
$company = Company::find(14);
if (!$company) {
    echo "Company 14 not found!\n";
    exit;
}

// Attach adminCorporate to both companies
if (!$company->users()->where('users.id', $adminCorporate->id)->exists()) {
    $company->users()->attach($adminCorporate->id, ['role_id' => 1]);
}
$comp5 = Company::find(5);
if ($comp5 && !$comp5->users()->where('users.id', $adminCorporate->id)->exists()) {
    $comp5->users()->attach($adminCorporate->id, ['role_id' => 1]);
}

// Clear old records for company 14
DB::table('transactions')->where('company_id', $company->id)->delete();
DB::table('invoice_items')->whereIn('itemable_id', DB::table('invoices')->where('company_id', $company->id)->pluck('id'))->delete();
DB::table('invoices')->where('company_id', $company->id)->delete();
DB::table('expenses')->where('company_id', $company->id)->delete();
DB::table('leads')->where('company_id', $company->id)->delete();
DB::table('products')->where('company_id', $company->id)->delete();
DB::table('vendors')->where('company_id', $company->id)->delete();
DB::table('customers')->where('company_id', $company->id)->delete();
DB::table('accounts')->where('company_id', $company->id)->delete();

// Accounts
$accAlfalah = Account::create([
    'company_id' => $company->id,
    'name' => 'Bank Alfalah - Operations A/C',
    'account_number' => 'BAFL-8829104',
    'opening_balance' => 3200000,
    'current_balance' => 3200000,
]);

$accFaysal = Account::create([
    'company_id' => $company->id,
    'name' => 'Faysal Bank - Islamic Freight A/C',
    'account_number' => 'FBL-1029384',
    'opening_balance' => 1850000,
    'current_balance' => 1850000,
]);

$accCash = Account::create([
    'company_id' => $company->id,
    'name' => 'Lahore Hub Petty Cash',
    'account_number' => 'CASH-LHR-01',
    'opening_balance' => 150000,
    'current_balance' => 150000,
]);

// Vendors
$v1 = Vendor::create(['company_id' => $company->id, 'name' => 'PSO Bulk Diesel Station', 'email' => 'pso.lhr@pso.com.pk', 'phone' => '+92 42 35789100']);
$v2 = Vendor::create(['company_id' => $company->id, 'name' => 'Nayatel Lahore Enterprise', 'email' => 'support@nayatel.com', 'phone' => '+92 51 111114444']);
$v3 = Vendor::create(['company_id' => $company->id, 'name' => 'HinoPak Fleet Maintenance', 'email' => 'service@hinopak.com', 'phone' => '+92 42 35889922']);

// Customers
$c1 = Customer::create(['company_id' => $company->id, 'created_by_user_id' => $user1->id, 'name' => 'Mian Kashif', 'company_name' => 'ChenOne Stores Ltd', 'email' => 'kashif@chenone.com.pk', 'phone' => '+92 300 8472910', 'address' => 'Gulberg III, Lahore']);
$c2 = Customer::create(['company_id' => $company->id, 'created_by_user_id' => $user1->id, 'name' => 'Nadeem Babar', 'company_name' => 'Servis Industries Ltd', 'email' => 'babar@servis.com.pk', 'phone' => '+92 321 4455667', 'address' => 'Muridke, G.T. Road, Lahore']);
$c3 = Customer::create(['company_id' => $company->id, 'created_by_user_id' => $user1->id, 'name' => 'Taimur Malik', 'company_name' => 'Kohinoor Mills Ltd', 'email' => 'taimur@kohinoormills.pk', 'phone' => '+92 333 9988221', 'address' => 'Kasur Road, Lahore']);
$c4 = Customer::create(['company_id' => $company->id, 'created_by_user_id' => $user1->id, 'name' => 'Asad Ullah', 'company_name' => 'Sapphire Retail Logistics', 'email' => 'asad@sapphire.com.pk', 'phone' => '+92 301 2233445', 'address' => 'Raiwind Road, Lahore']);
$c5 = Customer::create(['company_id' => $company->id, 'created_by_user_id' => $user1->id, 'name' => 'Irfan Sheikh', 'company_name' => 'Nishat Chunian Transport', 'email' => 'irfan@chunian.com.pk', 'phone' => '+92 345 6677889', 'address' => 'Ferozepur Road, Lahore']);

// Products
$p1 = Product::create(['company_id' => $company->id, 'name' => 'National Inter-City Cargo Dispatch', 'code' => 'FRT-NAT-01', 'type' => 'service', 'purchase_price' => 75000, 'sale_price' => 120000, 'stock_quantity' => 0, 'description' => '22-wheeler articulated container transport']);
$p2 = Product::create(['company_id' => $company->id, 'name' => 'Bonded Warehouse Pallet Storage (Monthly)', 'code' => 'WHS-PLT-M', 'type' => 'service', 'purchase_price' => 20000, 'sale_price' => 45000, 'stock_quantity' => 0, 'description' => 'Climate-controlled palletized storage']);
$p3 = Product::create(['company_id' => $company->id, 'name' => 'Teltonika GPS Fleet Tracker Device', 'code' => 'GPS-TEL-FMB', 'type' => 'product', 'purchase_price' => 18000, 'sale_price' => 28000, 'stock_quantity' => 45, 'description' => 'Live satellite tracking with fuel sensor integration']);

// Invoices
$invoices = [
    ['cust' => $c1, 'prod' => $p1, 'qty' => 4, 'paid' => 480000, 'status' => 'paid', 'acc' => $accAlfalah, 'num' => 'INV-AR-001', 'date' => '2026-10-20'],
    ['cust' => $c2, 'prod' => $p1, 'qty' => 7, 'paid' => 840000, 'status' => 'paid', 'acc' => $accFaysal, 'num' => 'INV-AR-002', 'date' => '2026-10-24'],
    ['cust' => $c3, 'prod' => $p2, 'qty' => 8, 'paid' => 200000, 'status' => 'partially_paid', 'acc' => $accAlfalah, 'num' => 'INV-AR-003', 'date' => '2026-10-28'],
    ['cust' => $c4, 'prod' => $p3, 'qty' => 22, 'paid' => 616000, 'status' => 'paid', 'acc' => $accAlfalah, 'num' => 'INV-AR-004', 'date' => '2026-11-02'],
    ['cust' => $c5, 'prod' => $p1, 'qty' => 3, 'paid' => 0, 'status' => 'unpaid', 'acc' => $accAlfalah, 'num' => 'INV-AR-005', 'date' => '2026-11-06'],
];

$seq = 200;
foreach ($invoices as $invCfg) {
    $subtotal = $invCfg['prod']->sale_price * $invCfg['qty'];
    $seq++;

    $inv = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $invCfg['cust']->id,
        'created_by_user_id' => $user1->id,
        'invoice_number' => $invCfg['num'],
        'sequence_number' => $seq,
        'date' => $invCfg['date'],
        'due_date' => date('Y-m-d', strtotime($invCfg['date'] . ' + 30 days')),
        'subtotal' => $subtotal,
        'discount' => 0,
        'tax' => 0,
        'total' => $subtotal,
        'paid_amount' => $invCfg['paid'],
        'status' => $invCfg['status'],
        'notes' => 'Al-Rahim Logistics — Certified Carrier NTN # 8829103-4',
    ]);

    InvoiceItem::create([
        'itemable_type' => Invoice::class,
        'itemable_id' => $inv->id,
        'product_id' => $invCfg['prod']->id,
        'description' => $invCfg['prod']->name,
        'quantity' => $invCfg['qty'],
        'unit_price' => $invCfg['prod']->sale_price,
        'cost_price' => $invCfg['prod']->purchase_price,
        'total_price' => $subtotal,
    ]);

    if ($invCfg['paid'] > 0) {
        Transaction::create([
            'company_id' => $company->id,
            'account_id' => $invCfg['acc']->id,
            'invoice_id' => $inv->id,
            'created_by_user_id' => $user1->id,
            'type' => 'income',
            'amount' => $invCfg['paid'],
            'date' => $invCfg['date'],
            'description' => "Received freight freight payment from {$invCfg['cust']->company_name}",
        ]);
        $invCfg['acc']->increment('current_balance', $invCfg['paid']);
    }
}

// Expenses
$expenses = [
    ['cat' => 'DHA Phase 8 Broadway Office Rent', 'amount' => 180000, 'acc' => $accAlfalah, 'vendor' => $v1, 'desc' => 'Office Lease October 2026'],
    ['cat' => 'Fleet Bulk Diesel & Lubricant', 'amount' => 240000, 'acc' => $accFaysal, 'vendor' => $v1, 'desc' => 'Diesel fuel delivery for heavy transport fleet'],
    ['cat' => 'Nayatel Dedicated Corporate Fiber', 'amount' => 35000, 'acc' => $accAlfalah, 'vendor' => $v2, 'desc' => 'Nayatel 100 Mbps internet connection'],
    ['cat' => 'Hino Fleet Preventive Maintenance', 'amount' => 95000, 'acc' => $accCash, 'vendor' => $v3, 'desc' => 'Brake pad and oil replacement for 3 trucks'],
];

foreach ($expenses as $e) {
    $exp = Expense::create([
        'company_id' => $company->id,
        'vendor_id' => $e['vendor']->id,
        'account_id' => $e['acc']->id,
        'created_by_user_id' => $user1->id,
        'category' => $e['cat'],
        'amount' => $e['amount'],
        'date' => '2026-10-25',
        'reference' => 'EXP-AR-' . rand(100, 999),
        'description' => $e['desc'],
    ]);

    Transaction::create([
        'company_id' => $company->id,
        'account_id' => $e['acc']->id,
        'expense_id' => $exp->id,
        'vendor_id' => $e['vendor']->id,
        'created_by_user_id' => $user1->id,
        'type' => 'expense',
        'amount' => $e['amount'],
        'date' => '2026-10-25',
        'description' => $e['desc'],
    ]);

    $e['acc']->decrement('current_balance', $e['amount']);
}

// Leads
Lead::create([
    'company_id' => $company->id,
    'created_by_user_id' => $user1->id,
    'title' => 'Faisalabad Textile Goods Road Cargo Dispatch',
    'contact_name' => 'Mian Ahsan',
    'email' => 'ahsan@faisalabadtextile.pk',
    'phone' => '+92 300 7788112',
    'value' => 750000,
    'status' => 'proposal_sent',
    'notes' => 'Long term 1-year contract for daily 2 trucks.',
]);

Lead::create([
    'company_id' => $company->id,
    'created_by_user_id' => $user1->id,
    'title' => 'Cold Chain Milk & Meat Transportation',
    'contact_name' => 'Chaudhry Nasir',
    'email' => 'nasir@punjabdairy.pk',
    'phone' => '+92 321 8899001',
    'value' => 1100000,
    'status' => 'qualified',
    'notes' => 'Requires reefer container with temperature sensor.',
]);

echo "Company 14 seeded successfully!\n";
