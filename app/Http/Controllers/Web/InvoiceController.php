<?php

namespace App\Http\Controllers\Web;

use App\Enums\PaymentMethod;
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
            ->when($request->search, fn ($q, $s) => $q
                ->where('invoice_number', 'like', "%{$s}%")
                ->orWhereHas('workOrder.customer', fn ($c) => $c->where('name', 'like', "%{$s}%"))
                ->orWhereHas('workOrder', fn ($w) => $w->where('order_number', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('billing.invoices.index', compact('invoices'));
    }

    public function create(WorkOrder $workOrder, BillingService $billing)
    {
        if ($workOrder->invoice) {
            return redirect()
                ->route('invoices.show', $workOrder->invoice)
                ->with('info', 'An invoice already exists for this work order.');
        }

        $breakdown = $billing->calculateInvoice($workOrder);

        return view('billing.invoices.create', compact('workOrder', 'breakdown'));
    }

    public function store(Request $request, WorkOrder $workOrder, BillingService $billing)
    {
        $request->validate(['service_charge' => ['nullable', 'numeric', 'min:0']]);

        try {
            $invoice = $billing->generateInvoice($workOrder, $request->service_charge ?: null);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('invoices.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice generated successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('workOrder.customer', 'workOrder.device', 'workOrder.partUsages.part', 'payments.receiver');
        $methods = collect(PaymentMethod::cases())->mapWithKeys(fn ($m) => [$m->value => $m->label()]);

        return view('billing.invoices.show', compact('invoice', 'methods'));
    }

    public function pay(RecordPaymentRequest $request, Invoice $invoice, BillingService $billing)
    {
        $amount = (float) $request->amount;

        if ($amount > $invoice->balance_due) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Payment amount exceeds the balance due.');
        }

        $billing->recordPayment($invoice, $amount, $request->method, $request->reference, auth()->id());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully.');
    }

    public function refund(Payment $payment, BillingService $billing)
    {
        if ($payment->status !== 'completed') {
            return redirect()
                ->route('invoices.show', $payment->invoice_id)
                ->with('error', 'Only completed payments can be refunded.');
        }

        $billing->refundPayment($payment);

        return redirect()
            ->route('invoices.show', $payment->invoice_id)
            ->with('success', 'Payment refunded successfully.');
    }
}
