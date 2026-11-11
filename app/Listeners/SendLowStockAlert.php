<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use App\Services\NotificationService;

class SendLowStockAlert
{
    public function handle(LowStockAlert $event): void
    {
        app(NotificationService::class)->notifyLowStock($event->part->name);
    }
}
