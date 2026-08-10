<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Part;
use App\Models\PartUsage;
use App\Models\Setting;
use App\Models\WorkOrder;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function billing(): BillingService
    {
        return app(BillingService::class);
    }

    private function setTaxRate(float $rate): void
    {
        Setting::updateOrCreate(
            ['key' => 'tax_rate'],
            ['value' => (string) $rate, 'group' => 'general']
        );
    }

    public function test_calculate_invoice_without_parts(): void
    {
        $this->setTaxRate(5);
        $workOrder = WorkOrder::factory()->create(['estimated_cost' => 2000, 'discount' => 0]);

        $breakdown = $this->billing()->calculateInvoice($workOrder);

        $this->assertEqualsWithDelta(2000, $breakdown['service_charge'], 0.01);
        $this->assertEqualsWithDelta(0, $breakdown['parts_cost'], 0.01);
        $this->assertEqualsWithDelta(2000, $breakdown['subtotal'], 0.01);
        $this->assertEqualsWithDelta(0, $breakdown['discount'], 0.01);
        $this->assertEqualsWithDelta(100, $breakdown['tax'], 0.01);
        $this->assertEqualsWithDelta(2100, $breakdown['total'], 0.01);
    }

    public function test_calculate_invoice_with_parts_and_discount(): void
    {
        $this->setTaxRate(5);
        $workOrder = WorkOrder::factory()->create(['estimated_cost' => 2000, 'discount' => 10]);

        PartUsage::create([
            'work_order_id' => $workOrder->id,
            'part_id' => Part::factory()->create(['selling_price' => 500])->id,
            'quantity' => 2,
            'unit_price' => 500,
            'total' => 1000,
        ]);

        $breakdown = $this->billing()->calculateInvoice($workOrder);

        $this->assertEqualsWithDelta(1000, $breakdown['parts_cost'], 0.01);
        $this->assertEqualsWithDelta(3000, $breakdown['subtotal'], 0.01);
        $this->assertEqualsWithDelta(300, $breakdown['discount'], 0.01);
        $this->assertEqualsWithDelta(135, $breakdown['tax'], 0.01);
        $this->assertEqualsWithDelta(2835, $breakdown['total'], 0.01);
    }

    public function test_calculate_invoice_allows_service_charge_override(): void
    {
        $workOrder = WorkOrder::factory()->create(['estimated_cost' => 5000, 'discount' => 0]);

        $breakdown = $this->billing()->calculateInvoice($workOrder, 1200);

        $this->assertEqualsWithDelta(1200, $breakdown['service_charge'], 0.01);
        $this->assertEqualsWithDelta(1200, $breakdown['total'], 0.01);
    }

    public function test_generate_invoice_creates_invoice_with_number_and_defaults(): void
    {
        $workOrder = WorkOrder::factory()->create(['estimated_cost' => 1500, 'discount' => 0]);

        $invoice = $this->billing()->generateInvoice($workOrder);

        $this->assertSame($workOrder->id, $invoice->work_order_id);
        $this->assertStringStartsWith('INV-' . now()->year . '-', $invoice->invoice_number);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertSame(0.0, (float) $invoice->paid_amount);
        $this->assertEqualsWithDelta(1500, $invoice->total, 0.01);
    }

    public function test_generate_invoice_throws_when_invoice_already_exists(): void
    {
        $workOrder = WorkOrder::factory()->create();
        Invoice::factory()->create(['work_order_id' => $workOrder->id]);

        $this->expectException(\RuntimeException::class);

        $this->billing()->generateInvoice($workOrder);
    }

    public function test_record_partial_payment_updates_status_to_partial(): void
    {
        $invoice = Invoice::factory()->create(['total' => 1500, 'paid_amount' => 0, 'status' => 'unpaid']);

        $payment = $this->billing()->recordPayment($invoice, 500, 'cash');

        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertSame(500.0, (float) $invoice->fresh()->paid_amount);
        $this->assertSame(1000.0, $invoice->fresh()->balance_due);
        $this->assertSame('completed', $payment->status);
    }

    public function test_record_full_payment_marks_invoice_paid(): void
    {
        $invoice = Invoice::factory()->create(['total' => 1500, 'paid_amount' => 0, 'status' => 'unpaid']);

        $this->billing()->recordPayment($invoice, 1500, 'bKash', 'REF-001');

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame(1500.0, (float) $invoice->fresh()->paid_amount);
        $this->assertSame(0.0, $invoice->fresh()->balance_due);
    }

    public function test_refund_payment_reverts_amount_and_status(): void
    {
        $invoice = Invoice::factory()->create(['total' => 1500, 'paid_amount' => 1500, 'status' => 'paid']);
        $payment = $invoice->payments()->create([
            'amount' => 1500,
            'method' => 'cash',
            'status' => 'completed',
        ]);

        $this->billing()->refundPayment($payment, 'REFUND-001');

        $this->assertSame('refunded', $payment->fresh()->status);
        $this->assertSame('unpaid', $invoice->fresh()->status);
        $this->assertSame(0.0, (float) $invoice->fresh()->paid_amount);
        $this->assertSame('REFUND-001', $payment->fresh()->reference);
    }

    public function test_next_invoice_number_increments_sequence(): void
    {
        $this->assertSame('INV-' . now()->year . '-00001', $this->billing()->nextInvoiceNumber());

        Invoice::factory()->create(['invoice_number' => 'INV-' . now()->year . '-00001']);

        $this->assertSame('INV-' . now()->year . '-00002', $this->billing()->nextInvoiceNumber());
    }
}
