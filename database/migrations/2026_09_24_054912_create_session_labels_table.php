<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan kolom session_id ke sensor_readings agar bisa
        // dikelompokkan per sesi pengujian
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->foreignId('measurement_session_id')
                ->nullable()
                ->after('device_id')
                ->constrained('measurement_sessions')
                ->nullOnDelete()
                ->comment('Sesi pengujian yang sedang aktif saat pembacaan dilakukan');

            $table->index('measurement_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_readings', function (Blueprint $table): void {
            $table->dropForeign(['measurement_session_id']);
            $table->dropColumn('measurement_session_id');
        });
    }
};
