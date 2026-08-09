<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case New = 'new';
    case Diagnosed = 'diagnosed';
    case Approved = 'approved';
    case Ready = 'ready';
    case InRepair = 'in_repair';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Diagnosed => 'Diagnosed',
            self::Approved => 'Approved',
            self::Ready => 'Ready for Repair',
            self::InRepair => 'In Repair',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Diagnosed => 'violet',
            self::Approved => 'cyan',
            self::Ready => 'amber',
            self::InRepair => 'orange',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    public static function workflow(): array
    {
        return [
            self::New->value => [self::Diagnosed, self::Cancelled],
            self::Diagnosed->value => [self::Approved, self::Cancelled],
            self::Approved->value => [self::Ready],
            self::Ready->value => [self::InRepair],
            self::InRepair->value => [self::Completed],
            self::Completed->value => [],
            self::Cancelled->value => [],
        ];
    }

    public function next(): ?self
    {
        return match ($this) {
            self::New => self::Diagnosed,
            self::Diagnosed => self::Approved,
            self::Approved => self::Ready,
            self::Ready => self::InRepair,
            self::InRepair => self::Completed,
            default => null,
        };
    }
}
