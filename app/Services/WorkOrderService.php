<?php

namespace App\Services;

use App\Enums\WorkOrderStatus;
use App\Jobs\SendCompletionAlert;
use App\Jobs\SendStatusUpdate;
use App\Models\Technician;
use App\Models\TechnicianAssignment;
use App\Models\WorkOrder;
use App\Models\WorkOrderNote;
use App\Models\WorkOrderStatusHistory;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function create(array $data, ?int $userId = null): WorkOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['order_number'] = $this->nextOrderNumber();
            $data['status'] = $data['status'] ?? WorkOrderStatus::New->value;
            $data['created_by'] = $userId;

            $workOrder = WorkOrder::create($data);

            $this->recordStatusChange($workOrder, $workOrder->status, $userId, 'Work order created');

            return $workOrder->load('customer', 'device', 'technician');
        });
    }

    public function assignTechnician(WorkOrder $workOrder, int $technicianId, ?int $userId = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $technicianId, $userId) {
            $workOrder->technician_id = $technicianId;
            $workOrder->save();

            TechnicianAssignment::updateOrCreate(
                ['work_order_id' => $workOrder->id],
                [
                    'technician_id' => $technicianId,
                    'assigned_at' => now(),
                ]
            );

            $this->recordStatusChange($workOrder, $workOrder->status, $userId, "Assigned to technician #{$technicianId}");

            $workOrder->load('technician');

            return $workOrder;
        });
    }

    /**
     * Transition a work order to a new status.
     *
     * @throws \InvalidArgumentException
     */
    public function changeStatus(WorkOrder $workOrder, string $status, ?int $userId = null, ?string $notes = null): WorkOrder
    {
        $allowed = WorkOrderStatus::workflow()[$workOrder->status] ?? [];

        if (! in_array($status, array_map(fn ($s) => $s->value, $allowed), true)) {
            throw new \InvalidArgumentException(
                "Cannot transition work order from '{$workOrder->status}' to '{$status}'."
            );
        }

        return DB::transaction(function () use ($workOrder, $status, $userId, $notes) {
            $workOrder->status = $status;

            if ($status === WorkOrderStatus::Completed->value) {
                $workOrder->completed_at = now();

                $workOrder->assignments()
                    ->whereNull('completed_at')
                    ->update(['completed_at' => now()]);

                SendCompletionAlert::dispatch($workOrder);
            } else {
                SendStatusUpdate::dispatch($workOrder, $notes);
            }

            $workOrder->save();

            $this->recordStatusChange($workOrder, $status, $userId, $notes);

            return $workOrder;
        });
    }

    public function recordStatusChange(WorkOrder $workOrder, string $status, ?int $userId = null, ?string $notes = null): WorkOrderStatusHistory
    {
        return WorkOrderStatusHistory::create([
            'work_order_id' => $workOrder->id,
            'status' => $status,
            'changed_by' => $userId,
            'notes' => $notes,
        ]);
    }

    public function addNote(WorkOrder $workOrder, string $note, ?int $userId = null): WorkOrderNote
    {
        return WorkOrderNote::create([
            'work_order_id' => $workOrder->id,
            'user_id' => $userId,
            'note' => $note,
        ]);
    }

    public function nextOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'WO-' . $year . '-';

        $last = WorkOrder::query()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    public function activeWorkloadByTechnician(): \Illuminate\Support\Collection
    {
        return Technician::withCount([
            'workOrders' => fn ($q) => $q->whereIn('status', [
                WorkOrderStatus::New->value,
                WorkOrderStatus::Diagnosed->value,
                WorkOrderStatus::Approved->value,
                WorkOrderStatus::Ready->value,
                WorkOrderStatus::InRepair->value,
            ]),
        ])->get();
    }
}
