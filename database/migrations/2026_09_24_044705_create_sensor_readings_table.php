<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')
                ->constrained('devices')
                ->cascadeOnDelete()
                ->comment('Reference to devices table');
            $table->unsignedSmallInteger('mq2')->comment('MQ-2 raw ADC value (0-4095)');
            $table->unsignedSmallInteger('mq3')->comment('MQ-3 raw ADC value (0-4095)');
            $table->unsignedSmallInteger('mq135')->comment('MQ-135 raw ADC value (0-4095)');
            $table->decimal('mq2_voltage', 5, 4)->nullable()->comment('MQ-2 voltage (0.0000-3.3000 V)');
            $table->decimal('mq3_voltage', 5, 4)->nullable()->comment('MQ-3 voltage (0.0000-3.3000 V)');
            $table->decimal('mq135_voltage', 5, 4)->nullable()->comment('MQ-135 voltage (0.0000-3.3000 V)');
            $table->timestamp('recorded_at')->useCurrent()->comment('Timestamp of sensor reading');
            $table->timestamps();

            // Indexes optimized for time-series queries and future ML feature extraction
            $table->index(['device_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
