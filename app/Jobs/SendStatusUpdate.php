<?php

namespace App\Jobs;

use App\Models\WorkOrder;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendStatusUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder, public ?string $message = null)
    {
    }

    public function handle(NotificationService $notifications): void
    {
        $notifications->notifyStatusUpdate($this->workOrder, $this->message);
    }
}
