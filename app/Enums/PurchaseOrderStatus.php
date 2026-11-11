<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
