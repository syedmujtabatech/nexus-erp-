<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\SequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SequenceAndAccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequence_recycling_on_deletion()
    {
        $this->seed();

        $user = User::where('role_id', 2)->first();
        $this->actingAs($user);
        $companyId = $user->current_company_id;

        $customer = Customer::first();

        // 1. Create Invoice 1 -> INV-001
        $seq1 = SequenceService::getNextNumber($companyId, 'invoice', 'INV-');
        $inv1 = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'created_by_user_id' => $user->id,
            'invoice_number' => $seq1['formatted'],
            'sequence_number' => $seq1['sequence_number'],
            'date' => now(),
            'total' => 100,
        ]);
        $this->assertEquals('INV-001', $inv1->invoice_number);

        // 2. Create Invoice 2 -> INV-002
        $seq2 = SequenceService::getNextNumber($companyId, 'invoice', 'INV-');
        $inv2 = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'created_by_user_id' => $user->id,
            'invoice_number' => $seq2['formatted'],
            'sequence_number' => $seq2['sequence_number'],
            'date' => now(),
            'total' => 200,
        ]);
        $this->assertEquals('INV-002', $inv2->invoice_number);

        // 3. Delete INV-001 (This triggers sequence recycling logic)
        $inv1->delete();

        // 4. Create Invoice 3 -> Should reuse INV-001!
        $seq3 = SequenceService::getNextNumber($companyId, 'invoice', 'INV-');
        $inv3 = Invoice::create([
            'company_id' => $companyId,
            'customer_id' => $customer->id,
            'created_by_user_id' => $user->id,
            'invoice_number' => $seq3['formatted'],
            'sequence_number' => $seq3['sequence_number'],
            'date' => now(),
            'total' => 300,
        ]);
        $this->assertEquals('INV-001', $inv3->invoice_number);
    }
}
