<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MlPredictionController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// ── Auth (guest) ─────────────────────────────────────────────────────
Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Aplikasi (auth) ──────────────────────────────────────────────────
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api/overview', [DashboardApiController::class, 'overview'])->name('dashboard.api.overview');
    Route::get('/dashboard/api/sensor', [DashboardApiController::class, 'sensor'])->name('dashboard.api.sensor');

    // ── Sesi Pengujian (Data Collection & Labeling) ──────────────
    Route::prefix('sessions')->name('sessions.')->group(function (): void {
        Route::get('/', [SessionController::class, 'index'])->name('index');
        Route::post('/', [SessionController::class, 'store'])->name('store');
        Route::get('/export-csv', [SessionController::class, 'exportCsv'])->name('export-csv');
        Route::get('/{session}', [SessionController::class, 'show'])->name('show');
        Route::patch('/{session}/end', [SessionController::class, 'end'])->name('end');
    });

    // ── Machine Learning Prediction ───────────────────────────────
    Route::prefix('ml')->name('ml.')->group(function (): void {
        Route::get('/', [MlPredictionController::class, 'index'])->name('index');
        Route::post('/predict', [MlPredictionController::class, 'predict'])->name('predict');
        Route::post('/predict-session/{session}', [MlPredictionController::class, 'predictSession'])->name('predict-session');
        Route::get('/health', [MlPredictionController::class, 'healthCheck'])->name('health');
    });

    // ── Manajemen Alat / Gudang ───────────────────────────────────
    Route::prefix('devices')->name('devices.')->group(function (): void {
        Route::get('/', [DeviceController::class, 'index'])->name('index');
        Route::post('/', [DeviceController::class, 'store'])->name('store');
        Route::get('/{device}/edit', [DeviceController::class, 'edit'])->name('edit');
        Route::patch('/{device}', [DeviceController::class, 'update'])->name('update');
        Route::delete('/{device}', [DeviceController::class, 'destroy'])->name('destroy');
        Route::post('/{device}/regenerate', [DeviceController::class, 'regenerate'])->name('regenerate');
        Route::post('/{device}/toggle', [DeviceController::class, 'toggle'])->name('toggle');
    });

    // ── Notifikasi ────────────────────────────────────────────────
    Route::prefix('alerts')->name('alerts.')->group(function (): void {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::post('/read-all', [AlertController::class, 'readAll'])->name('read-all');
        Route::patch('/{alert}/read', [AlertController::class, 'read'])->name('read');
    });
});
