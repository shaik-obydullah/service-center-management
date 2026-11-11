<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Calculate the invoice breakdown for a work order.
     *
     * @return array<string, float>
     */
    public function calculateInvoice(WorkOrder $workOrder, ?float $overrideServiceCharge = null): array
    {
        $serviceCharge = $overrideServiceCharge ?? (float) $workOrder->estimated_cost;
        $partsCost = (float) $workOrder->partUsages()->sum('total');
        $subtotal = $serviceCharge + $partsCost;

        $discountRate = (float) $workOrder->discount / 100;
        $discount = $subtotal * $discountRate;

        $taxRate = (float) setting('tax_rate', 0);
        $tax = ($subtotal - $discount) * ($taxRate / 100);

        $total = $subtotal - $discount + $tax;

        return [
            'service_charge' => $serviceCharge,
            'parts_cost' => $partsCost,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
        ];
    }

    public function generateInvoice(WorkOrder $workOrder, ?float $serviceCharge = null): Invoice
    {
        return DB::transaction(function () use ($workOrder, $serviceCharge) {
            if ($workOrder->invoice) {
                throw new \RuntimeException('An invoice already exists for this work order.');
            }

            $breakdown = $this->calculateInvoice($workOrder, $serviceCharge);

            $invoice = Invoice::create([
                'work_order_id' => $workOrder->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'service_charge' => $breakdown['service_charge'],
                'parts_cost' => $breakdown['parts_cost'],
                'subtotal' => $breakdown['subtotal'],
                'discount' => $breakdown['discount'],
                'tax' => $breakdown['tax'],
                'total' => $breakdown['total'],
                'paid_amount' => 0,
                'status' => InvoiceStatus::Unpaid->value,
            ]);

            return $invoice;
        });
    }

    public function recordPayment(Invoice $invoice, float $amount, string $method = PaymentMethod::Cash->value, ?string $reference = null, ?int $userId = null): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $userId) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'status' => 'completed',
                'received_by' => $userId,
            ]);

            $invoice->paid_amount += $amount;
            $invoice->status = $invoice->paid_amount >= $invoice->total
                ? InvoiceStatus::Paid->value
                : InvoiceStatus::Partial->value;
            $invoice->save();

            return $payment;
        });
    }

    public function refundPayment(Payment $payment, string $reference = null): Payment
    {
        return DB::transaction(function () use ($payment, $reference) {
            $payment->status = 'refunded';
            $payment->reference = $reference ?: $payment->reference;
            $payment->save();

            $invoice = $payment->invoice;
            $invoice->paid_amount = max(0, $invoice->paid_amount - $payment->amount);
            $invoice->status = $invoice->paid_amount >= $invoice->total
                ? InvoiceStatus::Paid->value
                : ($invoice->paid_amount > 0 ? InvoiceStatus::Partial->value : InvoiceStatus::Unpaid->value);
            $invoice->save();

            return $payment;
        });
    }

    public function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'INV-' . $year . '-';

        $last = Invoice::query()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
