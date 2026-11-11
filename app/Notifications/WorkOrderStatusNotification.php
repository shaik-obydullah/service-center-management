<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkOrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Work Order {$this->workOrder->order_number} update")
            ->greeting("Hello {$this->workOrder->customer->name},")
            ->line("Your work order {$this->workOrder->order_number} status is now: **{$this->workOrder->status}**.")
            ->line('Thank you for choosing our service center.')
            ->action('View Status', url('/work-orders/' . $this->workOrder->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'work_order_id' => $this->workOrder->id,
            'order_number' => $this->workOrder->order_number,
            'status' => $this->workOrder->status,
        ];
    }
}
