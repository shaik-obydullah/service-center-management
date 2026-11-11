<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('brand');
            $table->string('model');
            $table->string('serial_number')->nullable();
            $table->string('color')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_devices');
    }
};
