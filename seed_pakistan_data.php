<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
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

echo "Seeding Pakistani Enterprise Data for Nexus ERP & CRM...\n";

// 1. Roles
DB::statement("SET FOREIGN_KEY_CHECKS=0;");
Role::truncate();
DB::statement("SET FOREIGN_KEY_CHECKS=1;");

$superAdminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
$ownerRole = Role::create(['name' => 'Company Owner', 'slug' => 'company-owner']);
$staffRole = Role::create(['name' => 'Manager & Accountant', 'slug' => 'staff']);

// 2. User Update
$user = User::firstOrNew(['email' => 'syedmujtaba.dev@gmail.com']);
$user->name = 'Syed Mujtaba';
$user->password = Hash::make('mujtaba123');
$user->role_id = $superAdminRole->id;
$user->is_otp_verified = 1;
$user->phone = '+92 318 3284737';
$user->save();

// 3. Clean up spam companies (keep company 5 and create 1 additional clean company)
$keepCompanyId = 5;
DB::table('companies')->where('id', '!=', $keepCompanyId)->delete();
DB::table('company_user')->truncate();

// Update Company 5
$company = Company::find($keepCompanyId);
if (!$company) {
    $company = new Company();
    $company->id = 5;
}
$company->user_id = $user->id;
$company->name = 'Nexus Enterprise Solutions (Pvt) Ltd';
$company->email = 'info@nexuserp.pk';
$company->phone = '+92 21 35870091';
$company->address = 'Suite 402, FTC Building, Shahrah-e-Faisal, Karachi, Pakistan';
$company->currency = 'PKR';
$company->plan = 'premium';
$company->max_users = 200;
$company->max_invoices = 5000;
$company->plan_expires_at = now()->addYears(2);
$company->save();

// Create 2nd Company (Clean Regional Trading Company)
$company2 = Company::create([
    'user_id' => $user->id,
    'name' => 'Al-Rahim Logistics & Trading',
    'email' => 'contact@alrahimlogistics.com.pk',
    'phone' => '+92 42 35789900',
    'address' => 'Plot 18, Commercial Broadway, Phase 8, DHA, Lahore',
    'currency' => 'PKR',
    'plan' => 'advance',
    'max_users' => 50,
    'max_invoices' => 1000,
    'plan_expires_at' => now()->addYear(),
]);

// Attach user to both companies
$user->companies()->attach($company->id, ['role_id' => $superAdminRole->id]);
$user->companies()->attach($company2->id, ['role_id' => $ownerRole->id]);
$user->update(['current_company_id' => $company->id]);

// Clear old transactions, invoices, customers, leads, products, accounts for company 5
DB::table('transactions')->where('company_id', $company->id)->delete();
DB::table('invoice_items')->whereIn('itemable_id', DB::table('invoices')->where('company_id', $company->id)->pluck('id'))->delete();
DB::table('invoices')->where('company_id', $company->id)->delete();
DB::table('expenses')->where('company_id', $company->id)->delete();
DB::table('leads')->where('company_id', $company->id)->delete();
DB::table('products')->where('company_id', $company->id)->delete();
DB::table('vendors')->where('company_id', $company->id)->delete();
DB::table('customers')->where('company_id', $company->id)->delete();
DB::table('accounts')->where('company_id', $company->id)->delete();

// 4. Accounts
$accMeezan = Account::create([
    'company_id' => $company->id,
    'name' => 'Meezan Bank - Corporate Account',
    'account_number' => 'MBL-0102938475',
    'opening_balance' => 4500000,
    'current_balance' => 4500000,
]);

$accHBL = Account::create([
    'company_id' => $company->id,
    'name' => 'Habib Bank Limited (HBL) - Operations',
    'account_number' => 'HBL-0019283741',
    'opening_balance' => 2850000,
    'current_balance' => 2850000,
]);

$accCash = Account::create([
    'company_id' => $company->id,
    'name' => 'Head Office Petty Cash',
    'account_number' => 'CASH-HO-01',
    'opening_balance' => 250000,
    'current_balance' => 250000,
]);

// 5. Pakistani Customers
$customersData = [
    ['name' => 'Tariq Mehmood', 'company' => 'Al-Karam Textile Mills Ltd', 'email' => 'tariq.m@alkaram.com.pk', 'phone' => '+92 300 2145890', 'address' => 'Plot 45, Sector 16, Korangi Industrial Area, Karachi'],
    ['name' => 'Muhammad Usman', 'company' => 'Crescent Steel & Allied Products', 'email' => 'usman.crescent@steel.com.pk', 'phone' => '+92 321 8847291', 'address' => '9th Floor, Sidco Avenue Centre, Karachi'],
    ['name' => 'Bilal Ahmed', 'company' => 'Shalimar Tech Systems', 'email' => 'bilal@shalimartech.pk', 'phone' => '+92 333 4589021', 'address' => '24-C, Gulberg III, MM Alam Road, Lahore'],
    ['name' => 'Farhan Siddiqui', 'company' => 'Karachi Logistics Hub (Pvt) Ltd', 'email' => 'farhan@klh.com.pk', 'phone' => '+92 301 5567823', 'address' => 'Berth 12, Port Qasim Authority, Karachi'],
    ['name' => 'Zeeshan Khan', 'company' => 'Prime Auto Parts Distribution', 'email' => 'zeeshan@primeautoparts.pk', 'phone' => '+92 345 9901234', 'address' => 'Circular Road, Badami Bagh, Lahore'],
    ['name' => 'Hammad Sheikh', 'company' => 'National Agro Industries', 'email' => 'hammad@nationalagro.pk', 'phone' => '+92 302 7781290', 'address' => 'Sargodha Road, Industrial Estate, Faisalabad'],
    ['name' => 'Daniyal Raza', 'company' => 'Khyber Trading Corporation', 'email' => 'daniyal@khybertrade.pk', 'phone' => '+92 313 4450912', 'address' => 'Hayatabad Industrial Estate, Peshawar'],
    ['name' => 'Syed Ali Shah', 'company' => 'Indus Pharma Distributors', 'email' => 'ali.shah@induspharma.pk', 'phone' => '+92 300 8291045', 'address' => 'Sector 7-A, Korangi, Karachi'],
    ['name' => 'Mian Rashid Javaid', 'company' => 'Punjab Chemical & Polymers', 'email' => 'rashid@punjabchemicals.pk', 'phone' => '+92 308 6672314', 'address' => 'Vehari Road, Industrial Zone, Multan'],
    ['name' => 'Engr. Asim Qureshi', 'company' => 'Capital Engineering Works', 'email' => 'asim@capitaleng.pk', 'phone' => '+92 334 1129087', 'address' => 'Sector I-9/2, Industrial Area, Islamabad'],
    ['name' => 'Kamran Baig', 'company' => 'Frontier Flour & Food Mills', 'email' => 'kamran@frontierfood.pk', 'phone' => '+92 315 7890123', 'address' => 'G.T. Road, Rawalpindi'],
    ['name' => 'Naeem Akhter', 'company' => 'Mehran Agro Processing Corp', 'email' => 'naeem@mehranagro.pk', 'phone' => '+92 306 3345890', 'address' => 'Rice Mills Area, Larkana, Sindh'],
];

$customers = [];
foreach ($customersData as $c) {
    $customers[] = Customer::create([
        'company_id' => $company->id,
        'created_by_user_id' => $user->id,
        'name' => $c['name'],
        'company_name' => $c['company'],
        'email' => $c['email'],
        'phone' => $c['phone'],
        'address' => $c['address'],
        'credit_limit' => 2000000,
    ]);
}

// 6. Vendors
$vendorsData = [
    ['name' => 'Engro Chemicals & Supply Chain', 'email' => 'supply@engro.com.pk', 'phone' => '+92 21 35297500', 'address' => 'The Harbor Front, Clifton, Karachi'],
    ['name' => 'Packages Limited', 'email' => 'sales@packages.com.pk', 'phone' => '+92 42 35811541', 'address' => 'Shahrah-e-Roomi, P.O. Amer Sidhu, Lahore'],
    ['name' => 'Nayatel Telecom & Cloud', 'email' => 'corporate@nayatel.com', 'phone' => '+92 51 111 11 44 44', 'address' => '73-E, GD Arcade, Fazl-ul-Haq Road, Blue Area, Islamabad'],
    ['name' => 'Systems Limited Pakistan', 'email' => 'contact@systemsltd.com', 'phone' => '+92 42 35887600', 'address' => 'Software Technology Park, Ferozepur Road, Lahore'],
    ['name' => 'Pakistan State Oil (Fleet Fuel)', 'email' => 'cards@psopk.com', 'phone' => '+92 21 99203730', 'address' => 'PSO House, Khayaban-e-Iqbal, Clifton, Karachi'],
];

$vendors = [];
foreach ($vendorsData as $v) {
    $vendors[] = Vendor::create([
        'company_id' => $company->id,
        'name' => $v['name'],
        'email' => $v['email'],
        'phone' => $v['phone'],
        'address' => $v['address'],
    ]);
}

// 7. Products & Services
$productsData = [
    ['name' => 'Enterprise Cloud ERP License (Annual)', 'code' => 'LIC-ERP-01', 'type' => 'service', 'purchase' => 120000, 'sale' => 350000, 'stock' => 0, 'desc' => 'Multi-module SaaS Enterprise ERP Cloud Subscription'],
    ['name' => 'Dell PowerEdge R750 Rack Server', 'code' => 'SRV-DELL-750', 'type' => 'product', 'purchase' => 1150000, 'sale' => 1450000, 'stock' => 8, 'desc' => 'Xeon Silver, 64GB ECC RAM, 4x2TB Enterprise SAS RAID'],
    ['name' => 'Cisco Catalyst 48-Port Gigabit Switch', 'code' => 'NET-CS-48', 'type' => 'product', 'purchase' => 210000, 'sale' => 280000, 'stock' => 15, 'desc' => 'Managed Layer 3 Switch with PoE+ & SFP Uplinks'],
    ['name' => 'Custom API & Workflow Development', 'code' => 'DEV-SPRINT-01', 'type' => 'service', 'purchase' => 60000, 'sale' => 180000, 'stock' => 0, 'desc' => 'Dedicated engineering sprint for custom integrations'],
    ['name' => 'Biometric Fingerprint & RFID Terminal', 'code' => 'BIO-ZKT-40', 'type' => 'product', 'purchase' => 32000, 'sale' => 48000, 'stock' => 30, 'desc' => 'ZKTeco Time Attendance & Cloud Sync Device'],
    ['name' => '24/7 SLA Priority Support & AMC', 'code' => 'SLA-SUPP-QTR', 'type' => 'service', 'purchase' => 40000, 'sale' => 150000, 'stock' => 0, 'desc' => 'Quarterly mission-critical uptime guarantee'],
    ['name' => 'Fortinet FortiGate 60F Firewall', 'code' => 'SEC-FG-60F', 'type' => 'product', 'purchase' => 380000, 'sale' => 495000, 'stock' => 6, 'desc' => 'Enterprise Hardware Next-Gen Threat Protection'],
    ['name' => 'Industrial Thermal Barcode Printers', 'code' => 'PRN-ZBR-420', 'type' => 'product', 'purchase' => 58000, 'sale' => 82000, 'stock' => 20, 'desc' => 'Zebra High-Speed Barcode Label Printer for Warehouses'],
];

$products = [];
foreach ($productsData as $p) {
    $products[] = Product::create([
        'company_id' => $company->id,
        'name' => $p['name'],
        'code' => $p['code'],
        'type' => $p['type'],
        'purchase_price' => $p['purchase'],
        'sale_price' => $p['sale'],
        'stock_quantity' => $p['stock'],
        'description' => $p['desc'],
    ]);
}

// 8. Leads
$leadsData = [
    ['title' => 'Textile Mill ERP Automation', 'contact' => 'Salman Butt', 'email' => 'salman@gulltextiles.pk', 'phone' => '+92 300 9988112', 'val' => 850000, 'status' => 'proposal_sent', 'notes' => 'Discussed automated batch tracking and fabric roll barcode scanning.'],
    ['title' => 'Hospital Management & Pharmacy ERP', 'contact' => 'Dr. Naveed Iqbal', 'email' => 'dr.naveed@citycarehospital.pk', 'phone' => '+92 321 5566778', 'val' => 1250000, 'status' => 'qualified', 'notes' => 'OPD/IPD and pharmacy billing integration required.'],
    ['title' => 'Faisalabad Agro Cold Storage Tracking', 'contact' => 'Chaudhry Akram', 'email' => 'akram@coldstorage.pk', 'phone' => '+92 302 3344556', 'val' => 650000, 'status' => 'contacted', 'notes' => 'Requires lot tracking and daily weight ledger.'],
    ['title' => 'Logistics Fleet Telematics Integration', 'contact' => 'Major (R) Tariq', 'email' => 'tariq@shaheenlogistics.pk', 'phone' => '+92 333 1122334', 'val' => 1600000, 'status' => 'converted', 'notes' => 'Client approved proposal; invoice created.'],
    ['title' => 'Islamabad IT Park Fiber & Networking', 'contact' => 'Engr. Saad Farooq', 'email' => 'saad@itpark.gov.pk', 'phone' => '+92 334 7788990', 'val' => 980000, 'status' => 'proposal_sent', 'notes' => 'Submitted tender document for 48 switches and rack cabling.'],
    ['title' => 'Sialkot Sports Goods Export ERP', 'contact' => 'Khawaja Masood', 'email' => 'masood@forwardleathers.pk', 'phone' => '+92 300 4455667', 'val' => 520000, 'status' => 'new', 'notes' => 'Interested in packing list and export custom commercial invoicing.'],
    ['title' => 'Gujranwala Ceramics Point-of-Sale', 'contact' => 'Haji Abdul Rehman', 'email' => 'rehman@ceramicsworld.pk', 'phone' => '+92 345 6677889', 'val' => 420000, 'status' => 'converted', 'notes' => 'Deployed 3 POS stations and multi-store database.'],
    ['title' => 'Multan Ginning & Cotton Seed Ledger', 'contact' => 'Malik Riaz Ahmed', 'email' => 'riaz@cottonmills.pk', 'phone' => '+92 301 2233445', 'val' => 780000, 'status' => 'qualified', 'notes' => 'Reviewing demo next Monday.'],
    ['title' => 'Peshawar Marble Mining Distribution', 'contact' => 'Sher Khan Shinwari', 'email' => 'sherkhan@shinwaristones.pk', 'phone' => '+92 312 9900112', 'val' => 610000, 'status' => 'contacted', 'notes' => 'Sent introductory company profile via WhatsApp.'],
    ['title' => 'Quetta Dry Port Freight Operations', 'contact' => 'Mir Dost Baloch', 'email' => 'mirdost@balochcargo.pk', 'phone' => '+92 303 7788991', 'val' => 1900000, 'status' => 'new', 'notes' => 'Initial inquiry received through corporate website.'],
];

foreach ($leadsData as $l) {
    Lead::create([
        'company_id' => $company->id,
        'created_by_user_id' => $user->id,
        'title' => $l['title'],
        'contact_name' => $l['contact'],
        'email' => $l['email'],
        'phone' => $l['phone'],
        'value' => $l['val'],
        'status' => $l['status'],
        'notes' => $l['notes'],
    ]);
}

// 9. Invoices, Invoice Items & Transactions
$invoicesConfig = [
    ['cust_idx' => 0, 'prod_idx' => 0, 'qty' => 2, 'paid' => 700000, 'status' => 'paid', 'acc' => $accMeezan, 'num' => 'INV-2026-001', 'date' => '2026-10-15'],
    ['cust_idx' => 1, 'prod_idx' => 1, 'qty' => 1, 'paid' => 1450000, 'status' => 'paid', 'acc' => $accHBL, 'num' => 'INV-2026-002', 'date' => '2026-10-18'],
    ['cust_idx' => 2, 'prod_idx' => 2, 'qty' => 2, 'paid' => 300000, 'status' => 'partially_paid', 'acc' => $accMeezan, 'num' => 'INV-2026-003', 'date' => '2026-10-22'],
    ['cust_idx' => 3, 'prod_idx' => 3, 'qty' => 3, 'paid' => 540000, 'status' => 'paid', 'acc' => $accMeezan, 'num' => 'INV-2026-004', 'date' => '2026-10-25'],
    ['cust_idx' => 4, 'prod_idx' => 4, 'qty' => 5, 'paid' => 240000, 'status' => 'paid', 'acc' => $accCash, 'num' => 'INV-2026-005', 'date' => '2026-10-28'],
    ['cust_idx' => 5, 'prod_idx' => 5, 'qty' => 4, 'paid' => 350000, 'status' => 'partially_paid', 'acc' => $accHBL, 'num' => 'INV-2026-006', 'date' => '2026-11-01'],
    ['cust_idx' => 6, 'prod_idx' => 6, 'qty' => 1, 'paid' => 0, 'status' => 'unpaid', 'acc' => $accMeezan, 'num' => 'INV-2026-007', 'date' => '2026-11-03'],
    ['cust_idx' => 7, 'prod_idx' => 1, 'qty' => 1, 'paid' => 1450000, 'status' => 'paid', 'acc' => $accHBL, 'num' => 'INV-2026-008', 'date' => '2026-11-05'],
    ['cust_idx' => 8, 'prod_idx' => 7, 'qty' => 6, 'paid' => 492000, 'status' => 'paid', 'acc' => $accMeezan, 'num' => 'INV-2026-009', 'date' => '2026-11-07'],
    ['cust_idx' => 9, 'prod_idx' => 3, 'qty' => 2, 'paid' => 0, 'status' => 'unpaid', 'acc' => $accMeezan, 'num' => 'INV-2026-010', 'date' => '2026-11-09'],
];

$seq = 100;
foreach ($invoicesConfig as $invCfg) {
    $cust = $customers[$invCfg['cust_idx']];
    $prod = $products[$invCfg['prod_idx']];
    $subtotal = $prod->sale_price * $invCfg['qty'];
    $total = $subtotal;
    $seq++;

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $cust->id,
        'created_by_user_id' => $user->id,
        'invoice_number' => $invCfg['num'],
        'sequence_number' => $seq,
        'date' => $invCfg['date'],
        'due_date' => date('Y-m-d', strtotime($invCfg['date'] . ' + 30 days')),
        'subtotal' => $subtotal,
        'discount' => 0,
        'tax' => 0,
        'total' => $total,
        'paid_amount' => $invCfg['paid'],
        'status' => $invCfg['status'],
        'notes' => 'Standard Commercial Invoice — Pakistan Tax Compliant NTN # 4920192-3',
    ]);

    InvoiceItem::create([
        'itemable_type' => Invoice::class,
        'itemable_id' => $invoice->id,
        'product_id' => $prod->id,
        'description' => $prod->name,
        'quantity' => $invCfg['qty'],
        'unit_price' => $prod->sale_price,
        'cost_price' => $prod->purchase_price,
        'total_price' => $total,
    ]);

    if ($invCfg['paid'] > 0) {
        Transaction::create([
            'company_id' => $company->id,
            'account_id' => $invCfg['acc']->id,
            'invoice_id' => $invoice->id,
            'created_by_user_id' => $user->id,
            'type' => 'income',
            'amount' => $invCfg['paid'],
            'date' => $invCfg['date'],
            'description' => "Received payment for {$invCfg['num']} from {$cust->company_name}",
        ]);
        $invCfg['acc']->increment('current_balance', $invCfg['paid']);
    }
}

// 10. Operating Expenses
$expensesData = [
    ['cat' => 'Office Rent & Facilities', 'amount' => 250000, 'date' => '2026-10-05', 'ref' => 'RENT-FTC-OCT', 'desc' => 'Monthly Rent for Suite 402, FTC Tower Karachi', 'acc' => $accHBL, 'v_idx' => 0],
    ['cat' => 'Internet & Dedicated Cloud Bandwidth', 'amount' => 45000, 'date' => '2026-10-08', 'ref' => 'NYT-FIBER-882', 'desc' => 'Nayatel 100 Mbps Dedicated Corporate Fiber', 'acc' => $accMeezan, 'v_idx' => 2],
    ['cat' => 'K-Electric Commercial Electricity Bill', 'amount' => 185000, 'date' => '2026-10-12', 'ref' => 'KE-BILL-9092', 'desc' => 'Power utility bill for Karachi Technology Headquarters', 'acc' => $accMeezan, 'v_idx' => 0],
    ['cat' => 'Cloud Infrastructure & AWS Servers', 'amount' => 320000, 'date' => '2026-10-15', 'ref' => 'AWS-CLOUD-OCT', 'desc' => 'High availability production cluster and backup storage', 'acc' => $accHBL, 'v_idx' => 3],
    ['cat' => 'Company Fleet Fuel & Logistics', 'amount' => 85000, 'date' => '2026-10-20', 'ref' => 'PSO-FUEL-2026', 'desc' => 'PSO fleet card top-up for field maintenance staff', 'acc' => $accCash, 'v_idx' => 4],
    ['cat' => 'Staff Pantry & Office Supplies', 'amount' => 34000, 'date' => '2026-10-24', 'ref' => 'SUPP-PANTRY-01', 'desc' => 'Tea, coffee, printing paper, and office stationery', 'acc' => $accCash, 'v_idx' => 1],
    ['cat' => 'Legal Retainer & Corporate Audit', 'amount' => 150000, 'date' => '2026-11-02', 'ref' => 'AUDIT-LEGAL-26', 'desc' => 'Statutory audit filing and SECP compliance legal fees', 'acc' => $accHBL, 'v_idx' => 3],
    ['cat' => 'Staff Health & Accidental Insurance', 'amount' => 95000, 'date' => '2026-11-06', 'ref' => 'INS-EFU-NOV', 'desc' => 'Group health insurance coverage premium for engineering team', 'acc' => $accMeezan, 'v_idx' => 0],
];

foreach ($expensesData as $e) {
    $exp = Expense::create([
        'company_id' => $company->id,
        'vendor_id' => $vendors[$e['v_idx']]->id,
        'account_id' => $e['acc']->id,
        'created_by_user_id' => $user->id,
        'category' => $e['cat'],
        'amount' => $e['amount'],
        'date' => $e['date'],
        'reference' => $e['ref'],
        'description' => $e['desc'],
    ]);

    Transaction::create([
        'company_id' => $company->id,
        'account_id' => $e['acc']->id,
        'expense_id' => $exp->id,
        'vendor_id' => $vendors[$e['v_idx']]->id,
        'created_by_user_id' => $user->id,
        'type' => 'expense',
        'amount' => $e['amount'],
        'date' => $e['date'],
        'description' => "Expense: {$e['cat']} — {$e['desc']}",
    ]);

    $e['acc']->decrement('current_balance', $e['amount']);
}

echo "Seeding completed successfully! 50+ Pakistani Enterprise Records Created.\n";
