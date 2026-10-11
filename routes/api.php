<?php

use App\Http\Controllers\PredictionController;
use App\Http\Controllers\SensorDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-CASSAVA API Routes
|--------------------------------------------------------------------------
|
| These routes handle data from ESP32 devices and ML prediction results.
| No user authentication is used here - device authentication
| (API key / token) can be added in future phases.
|
*/

Route::prefix('v1')->group(function (): void {
    // Sensor data ingestion endpoint (called by ESP32)
    Route::post('/sensor-data', [SensorDataController::class, 'store'])
        ->name('api.sensor.store');

    // Fetch latest sensor reading
    Route::get('/sensor/latest', [SensorDataController::class, 'latest'])
        ->name('api.sensor.latest');

    // Fetch paginated sensor history
    Route::get('/sensor/history', [SensorDataController::class, 'history'])
        ->name('api.sensor.history');

    // Fetch latest SVM prediction
    Route::get('/prediction/latest', [PredictionController::class, 'latest'])
        ->name('api.prediction.latest');

    // Fetch paginated prediction history
    Route::get('/prediction/history', [PredictionController::class, 'history'])
        ->name('api.prediction.history');
});

// Unversioned aliases for ESP32 and frontend convenience
Route::post('/sensor-data', [SensorDataController::class, 'store']);
Route::get('/sensor/latest', [SensorDataController::class, 'latest']);
Route::get('/sensor/history', [SensorDataController::class, 'history']);
Route::get('/prediction/latest', [PredictionController::class, 'latest']);
Route::get('/prediction/history', [PredictionController::class, 'history']);
