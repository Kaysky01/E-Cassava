<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')
                ->constrained('devices')
                ->cascadeOnDelete()
                ->comment('Reference to device');
            $table->foreignId('measurement_session_id')
                ->nullable()
                ->constrained('measurement_sessions')
                ->nullOnDelete()
                ->comment('Optional reference to active measurement session');
            $table->enum('predicted_label', ['fresh', 'stale', 'rotten'])
                ->comment('Classification label predicted by ML model');
            $table->decimal('confidence', 5, 4)
                ->comment('Prediction confidence score between 0.0000 and 1.0000');
            $table->json('probabilities')
                ->comment('Class probabilities map (e.g. {"fresh": 0.95, "stale": 0.03, "rotten": 0.02})');
            $table->string('model_name')->default('SVM')
                ->comment('Classifier model identifier, default SVM');
            $table->unsignedSmallInteger('window_size')->default(30)
                ->comment('Number of sensor readings utilized in prediction window');
            $table->timestamp('predicted_at')->useCurrent()
                ->comment('Timestamp when prediction was executed');
            $table->timestamps();

            $table->index(['device_id', 'predicted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
