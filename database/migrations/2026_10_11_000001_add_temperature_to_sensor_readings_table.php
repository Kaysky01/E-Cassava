<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->decimal('temperature', 5, 2)->nullable()->after('mq135_voltage')
                ->comment('Suhu mesin dalam Celsius (sensor DS18B20/DHT, nullable)');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->dropColumn('temperature');
        });
    }
};
