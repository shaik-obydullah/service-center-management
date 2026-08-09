<?php

namespace App\Services;

use App\Models\WorkOrder;

class NotificationService
{
    /**
     * Notify the customer of a work order about their status.
     */
    public function notifyStatusUpdate(WorkOrder $workOrder, string $message = null): void
    {
        $message ??= "Your work order {$workOrder->order_number} status is now: {$workOrder->status}";

        $this->log($workOrder, $message);
    }

    public function notifyCompletion(WorkOrder $workOrder): void
    {
        $message = "Good news! Work order {$workOrder->order_number} is complete and ready for pickup.";

        $this->log($workOrder, $message);
    }

    public function notifyLowStock(string $partName): void
    {
        logger()->warning("Low stock alert: {$partName}");
    }

    protected function log(WorkOrder $workOrder, string $message): void
    {
        // Email / SMS delivery is wired via queued notifications (MAIL_MAILER=log in dev).
        $customer = $workOrder->customer;

        logger()->info("Notification to {$customer?->name} ({$customer?->phone}): {$message}");
    }
}
