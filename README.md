# 🌿 E-CASSAVA – Electronic Nose Monitoring System

Sistem Electronic Nose (E-Nose) untuk monitoring dan klasifikasi kondisi singkong berbasis **ESP32**, **Laravel 13**, **MySQL**, dan **Machine Learning SVM (Python FastAPI)**.

Produk utuh: **landing page publik → daftar/login → aplikasi** (dashboard AI, manajemen gudang/alat, sesi uji, prediksi ML, notifikasi). Satu user bisa punya banyak ESP32 (multi-gudang). Dwibahasa **Indonesia (default) & English** + **dark mode**.

---

## 🔄 Alur Sistem (Klasifikasi SVM Otomatis, Real-Time)

```
ESP32 (MQ-2, MQ-3, MQ-135)
  │ POST /api/sensor-data + header X-API-KEY (JSON ADC 0–4095)
  ▼
Laravel 13 REST API → validasi API key per alat → simpan ke sensor_readings (201, cepat)
  │ dispatchAfterResponse → ClassifySensorWindowJob (tidak memblokir ESP32)
  ▼
Ambil N=30 pembacaan terakhir (ML_WINDOW_SIZE) → kirim window ke FastAPI :8001/predict
  │ model SVM (StandardScaler → SVC) mengklasifikasi fresh / stale / rotten
  ▼
Hasil → tabel predictions → alert otomatis (stale/rotten) → dashboard (polling 4 dtk)
```

> Nilai ADC adalah **Nilai Respons Sensor (0–4095)**, bukan PPM. Konfigurasi via `.env`:
> `ML_API_URL`, `ML_TIMEOUT_SECONDS=5`, `ML_WINDOW_SIZE=30`, `ML_MODEL_NAME=SVM`.

---

## 🚀 Quick Start

### 1. Menjalankan Laravel
```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoSeeder   # demo: 1 user, 2 gudang, data sintetis
php artisan serve
```
Akses: **[http://localhost:8000](http://localhost:8000)** (landing publik).
Login demo: `demo@ecassava.id` / `password`.

### 2. Training model SVM & menjalankan Python ML Service
```bash
cd ml
py -m pip install -r requirements.txt
py generate_sample_data.py        # opsional: dataset sintetis untuk testing
py train.py --csv sample_dataset.csv   # atau CSV hasil /sessions/export-csv
uvicorn main:app --reload --port 8001
```

### 3. Queue worker (agar Job klasifikasi diproses di background)
```bash
php artisan queue:work
```

### 4. Automated test
```bash
php artisan test
```

### 5. Frontend (Tailwind + Alpine via Vite)
```bash
npm install && npm run build   # atau npm run dev saat development
```

---

## 🗄️ Skema Database

### `devices` (+ milik user, API key)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `user_id` | FK → users, cascade | Pemilik alat |
| `device_id` | string unik | `ECASSAVA-XXX` (dibuat otomatis) |
| `name` / `location` | string | Nama + lokasi/gudang |
| `api_key` | string unik, nullable | **Hash SHA-256** key (null = perangkat lawas terbuka) |
| `api_key_hint` | string, nullable | 4 karakter terakhir key |
| `is_active` | bool | Nonaktif = ESP32 ditolak (403) |
| `status` / `last_seen_at` | — | Online jika terlihat ≤ 2 menit |

### `alerts` (baru)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `user_id` / `device_id` | FK | Pemilik + sumber (null = sistem) |
| `type` | string | `stale`, `rotten`, `offline`, `info` |
| `title_id` / `title_en`, `body_id` / `body_en` | string/text | Pesan dwibahasa |
| `read_at` | timestamp nullable | Sudah dibaca? |

Dibuat otomatis oleh `ClassifySensorWindowJob` (transisi ke stale/rotten, anti-spam: hanya saat label berubah) dan `AlertService::checkOffline()` (offline > 10 menit, maks 1/hari/alat). Tabel lain (`sensor_readings`, `predictions`, `measurement_sessions`, `session_labels`) tidak berubah.

---

## 🔌 Endpoint API

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| `POST` | `/api/sensor-data` | X-API-KEY* | ESP32 kirim data (kontrak respons tetap: 201 + success/message/data/session_id) |
| `GET` | `/api/sensor/latest?device_id=...` | X-API-KEY* | Pembacaan terakhir |
| `GET` | `/api/sensor/history` | X-API-KEY* | Riwayat terpaginasi |
| `GET` | `/api/prediction/latest?device_id=...` | X-API-KEY* | Prediksi + `window_status` + `ml_service_online` |
| `GET` | `/api/prediction/history` | X-API-KEY* | Riwayat prediksi |
| `GET` | `/dashboard/api/overview` | login (web) | Ringkasan real-time milik sendiri (polling dashboard) |
| `GET` | `/dashboard/api/sensor?device_id=&range=` | login (web) | Data grafik (range 1h/24h/7d) |

\*Key wajib **hanya jika perangkat punya API key** (semua alat baru). Perangkat lawas tanpa key tetap terbuka (kompatibilitas mundur). Key dikirim via header `X-API-KEY` atau field `api_key`. Salah/hilang → `401`. Perilaku `POST /api/sensor-data` saat ML down tidak berubah: data tetap tersimpan (201).

### ➕ Cara tambah alat (petani)
1. Login → menu **Alat** → **+ Tambah Alat** → isi nama + lokasi.
2. Modal menampilkan **snippet ESP32 siap salin** (`WIFI`, `API_URL`, `DEVICE_ID`, `API_KEY`). Key penuh hanya tampil sekali; tersedia **Regenerate key**.
3. Tempel ke `esp32/ecassava_firmware.ino` (konstanta `API_KEY`, dikirim sebagai header `X-API-KEY`), upload → data masuk ke gudang yang benar.

### 🌐 Cara ganti bahasa
Toggle **ID/EN** di navbar (landing maupun aplikasi). Disimpan di session + cookie `locale`, default `id`. Seluruh teks UI memakai `lang/id` & `lang/en`.

---

## 📁 Struktur Proyek
- `app/` • Controller (Landing, Auth, Device, Alert, Dashboard + DashboardApi, Session, Sensor, Prediction, ML), Model (+ `Alert`), Request, Resource, Enum, Job (`ClassifySensorWindowJob` + alert), Service (`MlPredictionService`, `AlertService`, `InsightService` rule-based), Policy (`DevicePolicy`, `MeasurementSessionPolicy`), Middleware (`SetLocale`).
- `database/` • Migrasi (termasuk `alerts`, `devices.user_id`, `devices.location/api_key/is_active`), factory, seeder (`DemoSeeder`).
- `resources/views/` • Landing, auth, dashboard AI (badge + gauge + Chart.js + insight + polling 4 dtk), devices, alerts, sesi, ML; komponen reusable (`badge-status`, `stat-tile`, `gauge`, `empty-state`, `modal`, `locale-switcher`, `theme-toggle`).
- `esp32/` • Firmware Arduino C++ (MQ-2/3/135, LCD I2C, HTTP POST + `X-API-KEY`).
- `ml/` • Modul Python (FastAPI service SVM, training GridSearchCV, generator dataset). **Tidak diubah.**
- `tests/` • PHPUnit: `SensorMonitoringTest`, `SvmPredictionTest`, `EcassavaUpgradeTest` (isolasi antar user, API key, pembuatan alat, alert, bahasa).

## 📖 Dokumentasi Lengkap
Dokumentasi teknis hardware pinout, skema DB, REST API, fitur ML, dan pengujian:
👉 **[DOKUMENTASI.md](DOKUMENTASI.md)**
