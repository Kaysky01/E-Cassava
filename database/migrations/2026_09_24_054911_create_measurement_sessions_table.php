<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurement_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')
                ->constrained('devices')
                ->cascadeOnDelete();
            $table->string('name')->comment('Nama sesi, mis. "Singkong Batch 01 - Hari 1"');
            $table->enum('cassava_condition', ['fresh', 'stale', 'rotten'])
                ->nullable()
                ->comment('Label kondisi singkong yang diketahui (untuk training ML)');
            $table->text('notes')->nullable()->comment('Catatan tambahan dari peneliti');
            $table->timestamp('started_at')->comment('Waktu mulai sesi perekaman');
            $table->timestamp('ended_at')->nullable()->comment('Waktu akhir sesi, null jika masih berjalan');
            $table->timestamps();

            $table->index(['device_id', 'started_at']);
            $table->index('cassava_condition');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurement_sessions');
    }
};
