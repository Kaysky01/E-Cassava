<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-CASSAVA Application Configuration
    |--------------------------------------------------------------------------
    */

    /*
     * URL Python FastAPI ML service.
     * Ubah sesuai alamat server Python Anda.
     * Contoh: http://localhost:8001
     */
    'ml_api_url' => env('ML_API_URL', 'http://localhost:8001'),

    /*
     * Batas waktu (detik) untuk request ke ML service.
     */
    'ml_timeout_seconds' => (int) env('ML_TIMEOUT_SECONDS', 5),

    /*
     * Ukuran window (jumlah pembacaan sensor terakhir) untuk klasifikasi SVM.
     */
    'ml_window_size' => (int) env('ML_WINDOW_SIZE', 30),

    /*
     * Nama model classifier aktif.
     */
    'ml_model_name' => env('ML_MODEL_NAME', 'SVM'),

    /*
     * Batas kepercayaan minimum agar label dianggap yakin.
     * Di bawah ini → label ditampilkan sebagai "Belum yakin" dan alert ditahan.
     */
    'ml_confidence_threshold' => (float) env('ML_CONFIDENCE_THRESHOLD', 0.6),

    /*
     * Batas selisih probabilitas peringkat 1-2 (margin).
     * Di bawah ini → "Belum yakin" (dua kelas hampir sama kuat).
     */
    'ml_margin_threshold' => (float) env('ML_MARGIN_THRESHOLD', 0.15),

    /*
     * Batas menit untuk device dianggap ONLINE.
     * Jika last_seen_at lebih dari nilai ini → OFFLINE.
     */
    'device_online_threshold_minutes' => (int) env('DEVICE_ONLINE_THRESHOLD', 2),
];
