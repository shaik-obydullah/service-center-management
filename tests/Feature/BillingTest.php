<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PartUsage;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoices_index_renders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('Invoices');
    }

    public function test_user_can_generate_invoice_for_work_order(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create([
            'estimated_cost' => 1000,
            'discount' => 0,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('invoices.store', $workOrder), ['service_charge' => 1000])
            ->assertRedirect();

        $this->assertDatabaseCount('invoices', 1);

        $invoice = Invoice::first();
        $this->assertSame($workOrder->id, $invoice->work_order_id);
        $this->assertStringStartsWith('INV-' . now()->year . '-', $invoice->invoice_number);
        $this->assertEqualsWithDelta(1000, $invoice->total, 0.01);
        $this->assertSame('unpaid', $invoice->status);
    }

    public function test_invoice_breakdown_includes_parts_and_tax(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create([
            'estimated_cost' => 2000,
            'discount' => 10,
            'created_by' => $user->id,
        ]);

        PartUsage::create([
            'work_order_id' => $workOrder->id,
            'part_id' => \App\Models\Part::factory()->create(['selling_price' => 500])->id,
            'quantity' => 2,
            'unit_price' => 500,
            'total' => 1000,
        ]);

        \App\Models\Setting::updateOrCreate(['key' => 'tax_rate'], ['value' => '5', 'group' => 'general']);

        $this->actingAs($user)
            ->post(route('invoices.store', $workOrder))
            ->assertRedirect();

        $invoice = Invoice::first();

        // service (2000) + parts (1000) = 3000; discount 10% = 300; tax 5% on 2700 = 135; total = 2835
        $this->assertEqualsWithDelta(2835, $invoice->total, 0.01);
        $this->assertEqualsWithDelta(1000, $invoice->parts_cost, 0.01);
    }

    public function test_invoice_cannot_be_generated_twice(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);

        Invoice::factory()->create(['work_order_id' => $workOrder->id]);

        $this->actingAs($user)
            ->post(route('invoices.store', $workOrder))
            ->assertRedirect(route('invoices.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_user_can_record_payment_that_marks_invoice_paid(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);
        $invoice = Invoice::factory()->create([
            'work_order_id' => $workOrder->id,
            'total' => 1500,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->post(route('invoices.pay', $invoice), [
                'amount' => 1500,
                'method' => 'cash',
            ])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertSame(1500.0, (float) $invoice->fresh()->paid_amount);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1500,
            'status' => 'completed',
        ]);
    }

    public function test_payment_exceeding_balance_is_rejected(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);
        $invoice = Invoice::factory()->create([
            'work_order_id' => $workOrder->id,
            'total' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($user)
            ->post(route('invoices.pay', $invoice), [
                'amount' => 1500,
                'method' => 'cash',
            ])
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('error');

        $this->assertSame(0.0, (float) $invoice->fresh()->paid_amount);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_user_can_refund_a_payment(): void
    {
        $user = User::factory()->create();
        $workOrder = WorkOrder::factory()->create(['created_by' => $user->id]);
        $invoice = Invoice::factory()->create([
            'work_order_id' => $workOrder->id,
            'total' => 1500,
            'paid_amount' => 1500,
            'status' => 'paid',
        ]);
        $payment = $invoice->payments()->create([
            'amount' => 1500,
            'method' => 'cash',
            'status' => 'completed',
            'received_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('payments.refund', $payment))
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame('unpaid', $invoice->fresh()->status);
        $this->assertSame(0.0, (float) $invoice->fresh()->paid_amount);
    }
}
