<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index('user_id');
        });

        // Pindahkan data lama ke user default agar tidak hilang.
        $firstUserId = User::query()->orderBy('id')->value('id');

        if ($firstUserId) {
            Device::whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
