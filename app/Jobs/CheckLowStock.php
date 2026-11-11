<?php

namespace App\Jobs;

use App\Models\Part;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckLowStock implements ShouldQueue
{
    use Queueable;

    public function handle(NotificationService $notifications): void
    {
        Part::lowStock()->each(function (Part $part) use ($notifications) {
            $notifications->notifyLowStock($part->name);
        });
    }
}
