<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WorkOrder;
use App\Services\BillingService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->with('workOrder.customer', 'workOrder.device')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($invoices);
    }

    public function store(Request $request, WorkOrder $workOrder, BillingService $billing)
    {
        $request->validate(['service_charge' => ['nullable', 'numeric', 'min:0']]);

        try {
            $invoice = $billing->generateInvoice($workOrder, $request->service_charge ?: null);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Invoice generated.', 'invoice' => $invoice->load('workOrder')], 201);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('workOrder.customer', 'workOrder.device', 'workOrder.partUsages.part', 'payments');

        return response()->json($invoice);
    }

    public function pay(RecordPaymentRequest $request, Invoice $invoice, BillingService $billing)
    {
        $amount = (float) $request->amount;

        if ($amount > $invoice->balance_due) {
            return response()->json(['message' => 'Payment amount exceeds balance due.'], 422);
        }

        $payment = $billing->recordPayment($invoice, $amount, $request->method, $request->reference, $request->user()?->id);

        return response()->json(['message' => 'Payment recorded.', 'payment' => $payment, 'invoice' => $invoice->fresh()], 201);
    }

    public function refund(Payment $payment, BillingService $billing)
    {
        if ($payment->status !== 'completed') {
            return response()->json(['message' => 'Only completed payments can be refunded.'], 422);
        }

        $billing->refundPayment($payment);

        return response()->json(['message' => 'Payment refunded.', 'payment' => $payment->fresh()]);
    }
}
