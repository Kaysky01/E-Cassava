<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->string('location')->nullable()->after('name');
            $table->string('api_key', 128)->nullable()->unique()->after('last_seen_at')
                ->comment('SHA-256 hash API key perangkat (null = lawas/terbuka)');
            $table->string('api_key_hint', 8)->nullable()->after('api_key')
                ->comment('4 karakter terakhir key untuk identifikasi');
            $table->boolean('is_active')->default(true)->after('api_key_hint');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->dropColumn(['location', 'api_key', 'api_key_hint', 'is_active']);
        });
    }
};
