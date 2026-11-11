<?php

namespace App\Jobs;

use App\Models\WorkOrder;
use App\Services\BillingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoice implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder, public ?float $serviceCharge = null)
    {
    }

    public function handle(BillingService $billing): void
    {
        $billing->generateInvoice($this->workOrder, $this->serviceCharge);
    }
}
