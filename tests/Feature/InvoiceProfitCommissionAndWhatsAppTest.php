<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceProfitCommissionAndWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role_id' => 1,
            'is_otp_verified' => true,
        ]);

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'name' => 'Apex Solutions',
            'currency' => 'PKR',
        ]);

        $this->user->update(['current_company_id' => $this->company->id]);
        $this->actingAs($this->user);
    }

    public function test_customer_credit_limit_and_balance_tracking()
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'Karachi Retailers',
            'phone' => '03183284737',
            'credit_limit' => 50000.00,
        ]);

        $this->assertEquals(50000.00, $customer->credit_limit);
        $this->assertEquals(0, $customer->outstanding_balance);
        $this->assertFalse($customer->isCreditLimitExceeded(10000));
        $this->assertTrue($customer->isCreditLimitExceeded(60000));
    }

    public function test_invoice_creation_with_cost_profit_and_sales_commission()
    {
        $customer = Customer::create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'Lahore Wholesale',
            'phone' => '03001234567',
            'credit_limit' => 100000.00,
        ]);

        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'High-end Keyboard',
            'type' => 'product',
            'purchase_price' => 2000.00, // Cost
            'sale_price' => 3500.00,     // Sale
            'stock_quantity' => 50,
        ]);

        $postData = [
            'customer_id' => $customer->id,
            'date' => '2026-10-31',
            'sales_person_id' => $this->user->id,
            'commission_rate' => 10.0, // 10%
            'discount' => 500.00,
            'tax' => 0.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'description' => 'Mechanical Keyboard',
                    'quantity' => 2,
                    'cost_price' => 2000.00,
                    'unit_price' => 3500.00,
                ]
            ],
        ];

        $response = $this->post(route('invoices.store'), $postData);
        $response->assertRedirect();

        $invoice = Invoice::where('customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);

        // Verification of calculations:
        // Subtotal = 2 * 3500 = 7000
        $this->assertEquals(7000.00, $invoice->subtotal);
        // Total = 7000 - 500 = 6500
        $this->assertEquals(6500.00, $invoice->total);
        // Commission = (7000 - 500) * 10% = 650.00
        $this->assertEquals(650.00, $invoice->commission_amount);
        $this->assertEquals(10.0, $invoice->commission_rate);
        $this->assertEquals($this->user->id, $invoice->sales_person_id);

        // Cost of Goods Sold = 2 * 2000 = 4000
        $this->assertEquals(4000.00, $invoice->total_cost);

        // Gross Profit = (Subtotal - Total Cost) - Discount = (7000 - 4000) - 500 = 2500
        $this->assertEquals(2500.00, $invoice->total_profit);

        // Net Profit = Gross Profit - Commission = 2500 - 650 = 1850
        $this->assertEquals(1850.00, $invoice->net_profit);

        // Item level profit
        $item = $invoice->items->first();
        $this->assertEquals(2000.00, $item->cost_price);
        $this->assertEquals(3500.00, $item->unit_price);
        // Item profit = (3500 - 2000) * 2 = 3000
        $this->assertEquals(3000.00, $item->profit);
    }

    public function test_whatsapp_service_formatting_and_message_generation()
    {
        // International formatting
        $cleaned = WhatsAppService::formatPhoneNumber('0318-3284737');
        $this->assertEquals('923183284737', $cleaned);

        $customer = Customer::create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->user->id,
            'name' => 'Ali Khan',
            'phone' => '0318-3284737',
        ]);

        $invoice = Invoice::create([
            'company_id' => $this->company->id,
            'customer_id' => $customer->id,
            'created_by_user_id' => $this->user->id,
            'invoice_number' => 'INV-001',
            'sequence_number' => 1,
            'date' => '2026-10-31',
            'subtotal' => 5000.00,
            'total' => 5000.00,
            'paid_amount' => 1000.00,
            'status' => 'partially_paid',
        ]);

        $msg = WhatsAppService::buildInvoiceMessage($invoice);
        $this->assertStringContainsString('Ali Khan', $msg);
        $this->assertStringContainsString('INV-001', $msg);
        $this->assertStringContainsString('4,000.00', $msg); // Outstanding due balance

        $waUrl = WhatsAppService::getInvoiceWhatsAppUrl($invoice);
        $this->assertStringStartsWith('https://wa.me/923183284737?text=', $waUrl);

        // Test WhatsApp dispatch route
        $response = $this->get(route('invoices.whatsapp', $invoice->id));
        $response->assertRedirect($waUrl);
    }
}
