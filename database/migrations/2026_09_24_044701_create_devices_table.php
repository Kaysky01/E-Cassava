<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->string('device_id')->unique()->comment('Unique device identifier, e.g. ECASSAVA-001');
            $table->string('name')->comment('Human-readable device name');
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_seen_at')->nullable()->comment('Last time device sent data');
            $table->timestamps();

            $table->index('device_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
