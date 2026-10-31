<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Account;
use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CrmCompleteCrudAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_has_full_access()
    {
        $superAdmin = User::where('role_id', 1)->first();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->hasPermission('leads_view'));
        $this->assertTrue($superAdmin->hasPermission('random_nonexistent_permission'));

        $this->actingAs($superAdmin);
        $res = $this->get(route('dashboard'));
        $res->assertStatus(200);
        $res->assertSee('Platform Revenue');
        $res->assertSee('Registered Businesses');
    }

    public function test_subuser_granular_permissions()
    {
        $owner = User::where('role_id', 2)->first();
        $company = $owner->currentCompany;

        // Create subuser with only leads_view and leads_create
        $subuser = User::create([
            'name' => 'Sales Guy',
            'email' => 'sales@example.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
            'is_otp_verified' => true,
            'current_company_id' => $company->id,
        ]);

        $company->users()->attach($subuser->id, [
            'role_id' => 3,
            'permissions' => json_encode(['leads_view', 'leads_create']),
        ]);

        $this->assertTrue($subuser->hasPermission('leads_view'));
        $this->assertTrue($subuser->hasPermission('leads_create'));
        $this->assertFalse($subuser->hasPermission('leads_delete'));
        $this->assertFalse($subuser->hasPermission('invoices_view'));
    }

    public function test_customer_full_crud_and_show()
    {
        $owner = User::where('role_id', 2)->first();
        $this->actingAs($owner);

        // 1. Create
        $response = $this->post(route('customers.store'), [
            'name' => 'Acme Test Customer',
            'email' => 'test@acme.com',
            'phone' => '+123456789',
            'company_name' => 'Acme Global',
        ]);
        $response->assertRedirect();
        $customer = Customer::where('email', 'test@acme.com')->first();
        $this->assertNotNull($customer);

        // 2. Show Detail Page
        $resShow = $this->get(route('customers.show', $customer->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Acme Test Customer');
        $resShow->assertSee('Total Invoiced');

        // 3. Update
        $this->post(route('customers.update', $customer->id), [
            'name' => 'Acme Updated Customer',
            'email' => 'test@acme.com',
        ]);
        $customer->refresh();
        $this->assertEquals('Acme Updated Customer', $customer->name);

        // 4. Delete
        $resDel = $this->delete(route('customers.destroy', $customer->id));
        $resDel->assertRedirect(route('customers.index'));
        $this->assertNull(Customer::find($customer->id));
    }

    public function test_lead_crud_and_conversion()
    {
        $owner = User::where('role_id', 2)->first();
        $this->actingAs($owner);

        // 1. Create
        $this->post(route('leads.store'), [
            'title' => 'Big Enterprise Deal',
            'contact_name' => 'Dwight Schrute',
            'value' => 5000,
            'status' => 'new',
        ]);
        $lead = Lead::where('title', 'Big Enterprise Deal')->first();
        $this->assertNotNull($lead);

        // 2. Show
        $this->get(route('leads.show', $lead->id))->assertStatus(200);

        // 3. Update
        $this->post(route('leads.update', $lead->id), [
            'title' => 'Big Enterprise Deal Won',
            'contact_name' => 'Dwight Schrute',
            'value' => 6000,
            'status' => 'qualified',
        ]);
        $lead->refresh();
        $this->assertEquals(6000, $lead->value);

        // 4. Convert to customer & quote
        $resConvert = $this->post(route('leads.convert', $lead->id));
        $resConvert->assertRedirect();
        $lead->refresh();
        $this->assertEquals('converted', $lead->status);
        $this->assertNotNull($lead->customer_id);
    }

    public function test_expense_balance_deduction_and_refund_on_delete()
    {
        $owner = User::where('role_id', 2)->first();
        $this->actingAs($owner);

        $account = Account::create([
            'company_id' => $owner->current_company_id,
            'name' => 'Expense Test Account',
            'opening_balance' => 1000,
            'current_balance' => 1000,
        ]);

        // Create expense of 300
        $this->post(route('expenses.store'), [
            'category' => 'Cloud Servers',
            'amount' => 300,
            'date' => now()->format('Y-m-d'),
            'account_id' => $account->id,
        ]);

        $account->refresh();
        $this->assertEquals(700, $account->current_balance);

        $expense = Expense::where('category', 'Cloud Servers')->first();
        $this->assertNotNull($expense);

        // Delete expense -> refunds account!
        $this->delete(route('expenses.destroy', $expense->id));
        $account->refresh();
        $this->assertEquals(1000, $account->current_balance);
    }
}
