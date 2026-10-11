# 🌿 E-CASSAVA – Electronic Nose Monitoring System

Sistem pemantauan kondisi dan klasifikasi kesegaran singkong berbasis **Electronic Nose (E-Nose)** menggunakan **ESP32**, **Laravel 13**, **MySQL**, dan **Machine Learning SVM (Python FastAPI)** — klasifikasi **otomatis dan real-time** setiap ada data sensor baru.

---

## 📌 Daftar Isi
1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Spesifikasi Hardware & Pinout ESP32](#2-spesifikasi-hardware--pinout-esp32)
3. [Skema & Relasi Database](#3-skema--relasi-database)
4. [Dokumentasi REST API](#4-dokumentasi-rest-api)
5. [Fitur Web Dashboard](#5-fitur-web-dashboard)
6. [Modul Machine Learning](#6-modul-machine-learning)
7. [Panduan Instalasi & Menjalankan](#7-panduan-instalasi--menjalankan)
8. [Pengujian & Verifikasi](#8-pengujian--verifikasi)

---

## 1. Arsitektur Sistem

```
┌─────────────────┐       WiFi (HTTP POST)       ┌────────────────────────┐
│  ESP32 E-Nose   │ ───────────────────────────> │  Laravel 13 REST API   │
│  MQ-2, 3, 135   │   POST /api/sensor-data      │  simpan sensor_readings │
│  LCD I2C 16x2   │   (201 Created, cepat)       │  dispatchAfterResponse  │
└─────────────────┘                              └───────────┬────────────┘
                                                             │ ClassifySensorWindowJob
                                                             │ (tidak memblokir ESP32)
                                                             ▼
                                                 ┌────────────────────────┐
                                                 │  Ambil N=30 terakhir   │
                                                 │  (ML_WINDOW_SIZE)      │
                                                 └───────────┬────────────┘
                                                             │ POST :8001/predict (window)
                                                             ▼
                                                 ┌────────────────────────┐
                                                 │  Python ML Service     │
                                                 │  SVM: Scaler → SVC     │
                                                 │  fresh/stale/rotten    │
                                                 └───────────┬────────────┘
                                                             │ hasil: label+confidence+probs
                                                             ▼
                                                  ┌────────────────────────┐
                                                  │      MySQL Database    │
                                                  │  (devices, readings,   │
                                                  │   sessions, PREDICTIONS)│
                                                  └───────────┬────────────┘
                                                              │
                          ┌───────────────────────────────────┴───────────────────────────────────┐
                          ▼                                                                       ▼
              ┌────────────────────────┐                                              ┌────────────────────────┐
              │  Web Dashboard (Blade) │                                              │   Python ML Service    │
              │  - Card Klasifikasi SVM│ <─────────── HTTP JSON (Port 8001) ───────── │  - SVM (SVC)           │
              │  - Polling 4 detik     │                                              │  - 12 Ekstraksi Fitur  │
              │  - Chart.js Grafik     │                                              │  - FastAPI Microservice│
              │  - Riwayat Prediksi    │                                              │  - GridSearchCV        │
              └────────────────────────┘                                              └────────────────────────┘
```

**Aturan alur otomatis:** jika data `< N (30)` → lewati prediksi (*collecting data x/30*); jika ML service down/timeout 5 dtk → log error, data sensor tetap tersimpan, response ESP32 tetap 201.

---

## 2. Spesifikasi Hardware & Pinout ESP32

### Konfigurasi Pin Sensor
| Komponen | Pin ESP32 | Tipe Signal | Deskripsi |
|---|---|---|---|
| **MQ-2** | `GPIO 34` (ADC1_CH6) | Analog Input | Gas mudah terbakar, asap, LPG |
| **MQ-3** | `GPIO 35` (ADC1_CH7) | Analog Input | Alkohol, etanol, senyawa fermentasi |
| **MQ-135** | `GPIO 32` (ADC1_CH4) | Analog Input | Kualitas udara, gas amonia, sulfida |
| **LCD I2C SDA** | `GPIO 21` | Digital I/O | Komunikasi I2C data |
| **LCD I2C SCL** | `GPIO 22` | Digital I/O | Komunikasi I2C clock |

- **Resolusi ADC:** 12-bit (Nilai output: `0 – 4095`)
- **Tegangan Referensi:** `3.3V` (Attenuation: `ADC_11db`)
- **Interval Pembacaan Sensor:** Setiap 1 detik (lokal ke LCD)
- **Interval Pengiriman ke Server:** Setiap 5 detik via HTTP POST JSON

> ⚠️ **Catatan:** Nilai ADC (0–4095) merupakan **Nilai Respons Sensor**, bukan konsentrasi gas dalam PPM.

---

## 3. Skema & Relasi Database

### 1. Tabel `devices`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `device_id` | VARCHAR(50) (UNIQUE) | ID unik perangkat (contoh: `ECASSAVA-001`) |
| `name` | VARCHAR(255) | Nama label perangkat |
| `status` | ENUM('online','offline') | Status koneksi |
| `last_seen_at` | TIMESTAMP (NULL) | Waktu terakhir mengirim data sensor |
| `created_at`, `updated_at` | TIMESTAMP | Waktu pembuatan / update |

### 2. Tabel `measurement_sessions` (Dataset Training ML)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | ID sesi pengujian |
| `device_id` | BIGINT (FK) | Relasi ke tabel `devices.id` |
| `name` | VARCHAR(100) | Nama sesi pengujian sampel |
| `cassava_condition` | ENUM('fresh','stale','rotten') | Label kondisi singkong |
| `notes` | TEXT (NULL) | Catatan lingkungan / eksperimen |
| `started_at` | TIMESTAMP | Waktu mulai perekaman |
| `ended_at` | TIMESTAMP (NULL) | Waktu pengakhiran sesi |

### 3. Tabel `sensor_readings` (Time-series Log Data)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `device_id` | BIGINT (FK) | Relasi ke `devices.id` |
| `measurement_session_id` | BIGINT (FK, NULL) | Relasi ke `measurement_sessions.id` |
| `mq2` | SMALLINT UNSIGNED | Nilai ADC sensor MQ-2 (0–4095) |
| `mq3` | SMALLINT UNSIGNED | Nilai ADC sensor MQ-3 (0–4095) |
| `mq135` | SMALLINT UNSIGNED | Nilai ADC sensor MQ-135 (0–4095) |
| `mq2_voltage` | DECIMAL(5,4) | Nilai tegangan MQ-2 (Volt) |
| `mq3_voltage` | DECIMAL(5,4) | Nilai tegangan MQ-3 (Volt) |
| `mq135_voltage` | DECIMAL(5,4) | Nilai tegangan MQ-135 (Volt) |
| `recorded_at` | TIMESTAMP | Waktu pembacaan sensor |

### 4. Tabel `predictions` (Hasil Klasifikasi SVM Otomatis)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `device_id` | BIGINT (FK → devices, cascade delete) | Perangkat sumber |
| `measurement_session_id` | BIGINT (FK → measurement_sessions, NULL, null on delete) | Sesi aktif saat prediksi (jika ada) |
| `predicted_label` | ENUM('fresh','stale','rotten') | Hasil klasifikasi SVM |
| `confidence` | DECIMAL(5,4) | Skor kepercayaan 0.0000–1.0000 |
| `probabilities` | JSON | Probabilitas tiap kelas, mis. `{"fresh":0.92,"stale":0.05,"rotten":0.03}` |
| `model_name` | VARCHAR default `SVM` | Identitas model classifier |
| `window_size` | SMALLINT default 30 | Jumlah pembacaan window yang dipakai |
| `predicted_at` | TIMESTAMP | Waktu prediksi dijalankan |
| `created_at`, `updated_at` | TIMESTAMP | Waktu pembuatan / update |

- Index: `(device_id, predicted_at)` untuk query riwayat cepat.
- Relasi Eloquent: `Device hasMany predictions` (+ `latestPrediction`), `MeasurementSession hasMany predictions`, `Prediction belongsTo device & measurementSession`.

---

## 4. Dokumentasi REST API

### A. Simpan Data Sensor (Dari ESP32)
- **Endpoint:** `POST /api/sensor-data`
- **Headers:** `Content-Type: application/json`, `Accept: application/json`
- **Request Body:**
```json
{
  "device_id": "ECASSAVA-001",
  "mq2": 1234,
  "mq3": 856,
  "mq135": 1024,
  "mq2_voltage": 0.9944,
  "mq3_voltage": 0.6900,
  "mq135_voltage": 0.8247
}
```
- **Response Success (201 Created):**
```json
{
  "success": true,
  "message": "Sensor data saved successfully",
  "data": {
    "id": 1,
    "device_id": "ECASSAVA-001",
    "device_name": "E-CASSAVA Sensor 01",
    "mq2": 1234,
    "mq3": 856,
    "mq135": 1024,
    "mq2_voltage": 0.9944,
    "mq3_voltage": 0.69,
    "mq135_voltage": 0.8247,
    "recorded_at": "2026-10-02T06:00:00+00:00"
  },
  "session_id": null
}
```

---

### B. Ambil Data Sensor Terbaru
- **Endpoint:** `GET /api/sensor/latest` (atau dengan query parameter `?device_id=ECASSAVA-001`)
- **Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "device_id": "ECASSAVA-001",
    "device_name": "E-CASSAVA Sensor 01",
    "mq2": 1234,
    "mq3": 856,
    "mq135": 1024,
    "mq2_voltage": 0.9944,
    "mq3_voltage": 0.69,
    "mq135_voltage": 0.8247,
    "recorded_at": "2026-10-02T06:00:00+00:00"
  }
}
```

---

### C. Ambil Riwayat Data Sensor (Pagination)
- **Endpoint:** `GET /api/sensor/history?per_page=20&page=1`

---

### D. Prediksi SVM Terbaru (Hasil Otomatis dari Database)
- **Endpoint:** `GET /api/prediction/latest?device_id=ECASSAVA-001` (juga tersedia `/api/v1/prediction/latest`)
- **Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "device_id": "ECASSAVA-001",
    "predicted_label": "fresh",
    "label_text": "Segar",
    "confidence": 0.92,
    "confidence_percent": 92.0,
    "probabilities": {"fresh": 0.92, "stale": 0.05, "rotten": 0.03},
    "model_name": "SVM",
    "window_size": 30,
    "predicted_at": "2026-10-02T06:05:00+00:00"
  },
  "window_status": {"current": 30, "target": 30, "ready": true, "status_text": "Window data siap (30/30)"},
  "ml_service_online": true,
  "device_id": "ECASSAVA-001"
}
```
- Jika data `< ML_WINDOW_SIZE` → `data: null` + `window_status: {"current": 5, "target": 30, "ready": false}` (*Mengumpulkan data (5/30)*).

---

### E. Riwayat Prediksi SVM (Pagination)
- **Endpoint:** `GET /api/prediction/history?per_page=20` (juga tersedia `/api/v1/prediction/history`)

---

### F. Perilaku Otomatis `POST /api/sensor-data`
1. Data sensor selalu disimpan dulu → response `201 Created` cepat (tidak menunggu ML).
2. Laravel dispatch `ClassifySensorWindowJob::dispatchAfterResponse` → ambil N=30 terakhir (`ML_WINDOW_SIZE`) → `POST {ML_API_URL}/predict` dengan timeout 5 dtk (`ML_TIMEOUT_SECONDS`).
3. Jika window belum penuh → lewati prediksi (*collecting data*). Jika ML down/timeout → log error, data sensor tetap aman.

---

## 5. Fitur Web Dashboard

| Halaman | URL | Deskripsi |
|---|---|---|
| **Dashboard** | `/` | Card Hasil Klasifikasi SVM (badge hijau/kuning/merah, confidence, waktu, progress bar probabilitas) dengan polling otomatis per 4 detik, status *Mengumpulkan data (x/30)* / *ML service offline*, monitoring live status perangkat, 3 Card Respons Sensor (ADC), grafik Chart.js 50 titik terbaru, tabel log riwayat + tabel riwayat prediksi. |
| **Sesi Uji** | `/sessions` | Form perekaman data berlabel (*Segar / Mulai Busuk / Busuk*). Ketika sesi aktif, data yang dikirim ESP32 otomatis terasosiasi dengan sesi tersebut. |
| **Detail Sesi** | `/sessions/{id}` | Ekstraksi fitur statistik sesi (Mean, Std Dev, Min, Max), tabel log pembacaan, dan tombol evaluasi prediksi ML. |
| **Export CSV** | `/sessions/export-csv` | Mengunduh dataset 12-fitur terlabel dalam format CSV untuk input training model. |
| **Prediksi ML** | `/ml` | Hasil klasifikasi SVM otomatis terbaru + riwayat prediksi dari database, panel analisis manual per device, dan status Python ML service. |

---

## 6. Modul Machine Learning (SVM)

Model: **Support Vector Machine** dalam Pipeline `StandardScaler → SVC(probability=True)`, hyperparameter di-tuning via **GridSearchCV** (kernel rbf/linear, C, gamma) dengan **stratified cross-validation**. Input inference berupa **window array pembacaan sensor**; 12 fitur diekstrak **di sisi Python** agar konsisten dengan training.

### 12 Fitur yang Diekstrak per Sesi:
1. `mq2_mean`, `mq3_mean`, `mq135_mean` (Rata-rata respons sensor)
2. `mq2_std`, `mq3_std`, `mq135_std` (Standar deviasi / fluktuasi sinyal)
3. `mq2_min`, `mq3_min`, `mq135_min` (Nilai minimum respons)
4. `mq2_max`, `mq3_max`, `mq135_max` (Nilai puncak / peak respons)

### 3 Kategori Klasifikasi:
- 🟢 **fresh (Segar):** Respons gas rendah, pola udara bersih.
- 🟡 **stale (Mulai Busuk):** Terjadi peningkatan MQ-3 akibat fermentasi & pelepasan uap alkohol.
- 🔴 **rotten (Busuk):** Respons tinggi pada MQ-2, MQ-3, dan MQ-135 akibat gas amonia & senyawa volatil pembusukan.

### Struktur Direktori `ml/`:
- [`ml/main.py`](file:///e:/laragon/www/Cassava/ml/main.py): FastAPI microservice SVM (port 8001). Endpoint: `POST /predict` (input window array → label + confidence + probabilitas tiap kelas), `GET /health` (status + info model), `GET /model-info`. Error ditangani: model belum ada (503), data kurang (422), input tidak valid (422).
- [`ml/train.py`](file:///e:/laragon/www/Cassava/ml/train.py): Script training model SVM dari file CSV (menampilkan accuracy, classification report, confusion matrix; menyimpan `.joblib` + metadata fitur/kelas/tanggal/akurasi).
- [`ml/generate_sample_data.py`](file:///e:/laragon/www/Cassava/ml/generate_sample_data.py): Generator dataset sintetis untuk testing.
- [`ml/requirements.txt`](file:///e:/laragon/www/Cassava/ml/requirements.txt): Library Python (FastAPI, Scikit-learn, Pandas, Numpy, Joblib, Pydantic, Uvicorn).

---

## 7. Panduan Instalasi & Menjalankan

### A. Setup Laravel & MySQL
1. Pastikan MySQL di Laragon aktif.
2. Konfigurasi file [`.env`](file:///e:/laragon/www/Cassava/.env):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cassava
   DB_USERNAME=root
   DB_PASSWORD=

   ML_API_URL=http://localhost:8001
   ML_TIMEOUT_SECONDS=5
   ML_WINDOW_SIZE=30
   ML_MODEL_NAME=SVM
   DEVICE_ONLINE_THRESHOLD=2
   ```
3. Jalankan migrasi & seeder:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
4. Jalankan web server Laravel:
   ```bash
   php artisan serve
   ```
   Buka browser di **[http://localhost:8000](http://localhost:8000)**

---

### B. Setup ESP32
1. Buka file [`esp32/ecassava_firmware.ino`](file:///e:/laragon/www/Cassava/esp32/ecassava_firmware.ino) di Arduino IDE.
2. Install library **LiquidCrystal_I2C** via Library Manager.
3. Sesuaikan konfigurasi WiFi & IP server:
   ```cpp
   const char* WIFI_SSID     = "NAMA_WIFI_ANDA";
   const char* WIFI_PASSWORD = "PASSWORD_WIFI";
   const char* API_URL       = "http://IP_KOMPUTER_ANDA:8000/api/sensor-data";
   const char* DEVICE_ID     = "ECASSAVA-001";
   ```
4. Upload firmware ke board ESP32.

---

### C. Setup & Menjalankan Python ML Service
1. Buka terminal di folder `ml`:
   ```bash
   cd ml
   py -m pip install -r requirements.txt
   ```
2. Latih model SVM dengan data (atau buat data sampel):
   ```bash
   # Opsi 1: Pakai dummy data untuk testing
   py generate_sample_data.py
   py train.py --csv sample_dataset.csv

   # Opsi 2: Pakai dataset riil hasil export
   py train.py --csv ecassava_training_data_xxx.csv
   ```
   Training menampilkan accuracy, classification report, confusion matrix, lalu menyimpan `models/ecassava_model.joblib` + `models/model_meta.json` (fitur, kelas, tanggal training, akurasi, best params).
3. Jalankan FastAPI service:
   ```bash
   uvicorn main:app --reload --port 8001
   ```
4. Jalankan queue worker Laravel (wajib agar Job klasifikasi otomatis berjalan di background):
   ```bash
   php artisan queue:work
   ```

---

## 8. Pengujian & Verifikasi

### Menjalankan Automated Test Laravel
```bash
php artisan test
```
*Suite mencakup Dashboard, API Sensor, Validasi Data, Sesi Uji, dan `SvmPredictionTest`: prediksi tersimpan saat window penuh, ML down tidak menggagalkan data sensor, window belum penuh dilewati, serta status collecting via `GET /api/prediction/latest`.*

### Pengujian API dengan cURL
```bash
curl -X POST http://localhost:8000/api/sensor-data \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"device_id\":\"ECASSAVA-001\",\"mq2\":1200,\"mq3\":800,\"mq135\":950,\"mq2_voltage\":0.96,\"mq3_voltage\":0.64,\"mq135_voltage\":0.76}"
```
