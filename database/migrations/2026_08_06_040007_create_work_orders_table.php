<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('customer_devices')->restrictOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('repair_service_id')->nullable()->constrained()->nullOnDelete();
            $table->text('problem_description');
            $table->text('diagnosis')->nullable();
            $table->string('priority')->default('medium');
            $table->string('status')->default('new');
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->decimal('discount', 5, 2)->default(0);
            $table->date('estimated_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index('technician_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
