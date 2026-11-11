<?php

namespace App\Enums;

enum WorkOrderPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'slate',
            self::Medium => 'blue',
            self::High => 'amber',
            self::Urgent => 'red',
        };
    }
}
