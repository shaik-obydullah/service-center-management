<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('device_type_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->integer('estimated_time_hours')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_services');
    }
};
