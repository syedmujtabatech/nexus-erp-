<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Account;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchasesAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_purchase_creation_increments_stock_and_payment_deducts_bank()
    {
        $owner = User::where('role_id', 2)->first();
        $company = $owner->currentCompany;
        $this->actingAs($owner);

        // 1. Create a vendor
        $vendor = Vendor::create([
            'company_id' => $company->id,
            'name' => 'Al-Madina Supplier',
            'email' => 'supplier@almadina.com',
            'phone' => '03001234567',
        ]);

        // 2. Create a product with initial stock 10
        $product = Product::create([
            'company_id' => $company->id,
            'name' => 'Mechanical Keyboards',
            'code' => 'KB-01',
            'type' => 'product',
            'purchase_price' => 50.00,
            'sale_price' => 80.00,
            'stock_quantity' => 10,
        ]);

        // 3. Create a Bank Account with $5000
        $account = Account::create([
            'company_id' => $company->id,
            'name' => 'Standard Chartered Bank',
            'account_number' => 'SCB-9988',
            'opening_balance' => 5000.00,
            'current_balance' => 5000.00,
        ]);

        // 4. Record a purchase of 20 units @ $45.00 = $900.00
        $response = $this->post(route('purchases.store'), [
            'vendor_id' => $vendor->id,
            'date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => 'Mechanical Keyboards Lot A',
                    'quantity' => 20,
                    'unit_price' => 45.00,
                ],
            ],
            'notes' => 'Received at warehouse gate 2',
        ]);

        $response->assertRedirect();

        // Verify stock has automatically incremented from 10 to 30!
        $this->assertEquals(30, $product->fresh()->stock_quantity);

        // Verify purchase was recorded
        $purchase = Purchase::where('vendor_id', $vendor->id)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals(900.00, $purchase->total);
        $this->assertEquals(0, $purchase->paid_amount);
        $this->assertEquals('unpaid', $purchase->status);

        // 5. Pay Supplier $500.00 from Bank Account
        $payResponse = $this->post(route('purchases.pay', $purchase->id), [
            'account_id' => $account->id,
            'amount' => 500.00,
            'date' => now()->format('Y-m-d'),
            'notes' => 'Online wire payment',
        ]);

        $payResponse->assertRedirect();

        // Verify purchase status is now partially_paid
        $purchase->refresh();
        $this->assertEquals(500.00, $purchase->paid_amount);
        $this->assertEquals('partially_paid', $purchase->status);

        // Verify bank balance was deducted: 5000 - 500 = 4500
        $this->assertEquals(4500.00, $account->fresh()->current_balance);
    }

    public function test_purchase_deletion_reverses_product_stock()
    {
        $owner = User::where('role_id', 2)->first();
        $company = $owner->currentCompany;
        $this->actingAs($owner);

        $vendor = Vendor::create([
            'company_id' => $company->id,
            'name' => 'Global Electronics',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'name' => 'Gaming Mouse',
            'purchase_price' => 20.00,
            'sale_price' => 35.00,
            'stock_quantity' => 5,
        ]);

        // Purchase 15 units -> Stock becomes 20
        $this->post(route('purchases.store'), [
            'vendor_id' => $vendor->id,
            'date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => 'Gaming Mouse Batch',
                    'quantity' => 15,
                    'unit_price' => 20.00,
                ],
            ],
        ]);

        $this->assertEquals(20, $product->fresh()->stock_quantity);

        $purchase = Purchase::where('vendor_id', $vendor->id)->first();

        // Delete purchase -> Stock should automatically decrease by 15 back to 5!
        $this->delete(route('purchases.destroy', $purchase->id));

        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_company_saas_plan_update()
    {
        $owner = User::where('role_id', 2)->first();
        $company = $owner->currentCompany;
        $this->actingAs($owner);

        $res = $this->post(route('companies.plan', $company->id), [
            'plan' => 'advance',
        ]);

        $res->assertRedirect();
        $this->assertEquals('advance', $company->fresh()->plan);
    }

    public function test_user_profile_update()
    {
        $owner = User::where('role_id', 2)->first();
        $this->actingAs($owner);

        $res = $this->post(route('profile.update'), [
            'name' => 'Syed Mujtaba Updated',
            'email' => $owner->email,
            'phone' => '+923183284737',
        ]);

        $res->assertRedirect();
        $this->assertEquals('Syed Mujtaba Updated', $owner->fresh()->name);
        $this->assertEquals('+923183284737', $owner->fresh()->phone);
    }
}
