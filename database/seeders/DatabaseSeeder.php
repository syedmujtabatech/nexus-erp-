<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Account;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']); // ID 1
        $ownerRole = Role::create(['name' => 'Company Owner', 'slug' => 'company-owner']); // ID 2
        $subUserRole = Role::create(['name' => 'Sub User / Staff', 'slug' => 'staff']); // ID 3

        // 2. Super Admin User
        $superAdmin = User::create([
            'name' => 'System Super Admin',
            'email' => 'admin@crm.com',
            'phone' => '+100000000',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'is_otp_verified' => true,
        ]);

        // 3. Demo Owner User 1
        $owner1 = User::create([
            'name' => 'John Doe (Owner)',
            'email' => 'owner@acme.com',
            'phone' => '+199988877',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'is_otp_verified' => true,
        ]);

        // Create Company 1 for Owner 1
        $company1 = Company::create([
            'user_id' => $owner1->id,
            'name' => 'Acme Corporation',
            'email' => 'info@acme.com',
            'phone' => '+111222333',
            'address' => '123 Tech Avenue, New York, USA',
            'currency' => 'USD',
        ]);

        // Create Company 2 for Owner 1 (To test Multi-Company popup & switching)
        $company2 = Company::create([
            'user_id' => $owner1->id,
            'name' => 'Acme Logistics Ltd',
            'email' => 'logistics@acme.com',
            'phone' => '+111222444',
            'address' => '456 Freight Road, Texas, USA',
            'currency' => 'PKR',
        ]);

        // Attach user to companies
        $owner1->companies()->attach($company1->id, ['role_id' => 2]);
        $owner1->companies()->attach($company2->id, ['role_id' => 2]);

        // Set current company for owner
        $owner1->update(['current_company_id' => $company1->id]);

        // Create Sub-user for Company 1
        $staff1 = User::create([
            'name' => 'Sarah Sales Rep',
            'email' => 'sarah@acme.com',
            'phone' => '+111222555',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'is_otp_verified' => true,
            'current_company_id' => $company1->id,
        ]);
        $staff1->companies()->attach($company1->id, ['role_id' => 3, 'permissions' => json_encode(['view_leads', 'create_invoice'])]);

        // Login context as owner1 to set tenant scope for seeders
        auth()->login($owner1);

        // Seed Accounts for Company 1
        Account::create(['company_id' => $company1->id, 'name' => 'Petty Cash', 'account_number' => 'CASH-01', 'opening_balance' => 1000, 'current_balance' => 1000]);
        Account::create(['company_id' => $company1->id, 'name' => 'Chase Business Bank', 'account_number' => 'BANK-8899', 'opening_balance' => 50000, 'current_balance' => 50000]);

        // Seed Products & Services for Company 1
        Product::create(['company_id' => $company1->id, 'name' => 'Web Development Package', 'code' => 'DEV-01', 'type' => 'service', 'purchase_price' => 500, 'sale_price' => 1500, 'stock_quantity' => 0, 'description' => 'Full stack custom web solution']);
        Product::create(['company_id' => $company1->id, 'name' => 'CRM SaaS License', 'code' => 'LIC-01', 'type' => 'product', 'purchase_price' => 50, 'sale_price' => 200, 'stock_quantity' => 100, 'description' => 'Annual SaaS seat license']);

        // Seed Customers & Vendors for Company 1
        Customer::create(['company_id' => $company1->id, 'created_by_user_id' => $owner1->id, 'name' => 'Global Tech LLC', 'email' => 'contact@globaltech.com', 'phone' => '+15550001', 'company_name' => 'Global Tech']);
        Vendor::create(['company_id' => $company1->id, 'name' => 'Amazon Web Services', 'email' => 'billing@aws.com', 'phone' => '+18000000', 'address' => 'Seattle, USA']);
    }
}
