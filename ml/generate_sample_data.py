"""
E-CASSAVA – Generator Sample Data (Dummy Dataset)
=================================================
Script ini menghasilkan dataset sintetis untuk menguji alur Machine Learning
tanpa harus menunggu pengumpulan data sensor riil.

Karakteristik Sensor:
- fresh  (Segar)       : MQ-2 rendah, MQ-3 rendah, MQ-135 rendah
- stale  (Mulai Busuk) : MQ-2 sedang, MQ-3 meningkat (fermentasi/alkohol), MQ-135 sedang
- rotten (Busuk)       : MQ-2 tinggi, MQ-3 tinggi, MQ-135 sangat tinggi (gas amonia/sulfur)

Cara menjalankan:
    python generate_sample_data.py
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta

np.random.seed(42)

def _row(session_id, session_name, cls, mq2_m, mq3_m, mq135_m, start_t, std_lo=15, std_hi=120):
    """Satu baris sesi sintetis dengan noise realistis (std 10–60 seperti alat riil)."""
    mq2_m = float(np.clip(mq2_m, 100, 4000))
    mq3_m = float(np.clip(mq3_m, 100, 4000))
    mq135_m = float(np.clip(mq135_m, 100, 4000))
    end_t = start_t + timedelta(minutes=5)
    return {
        'session_id': session_id,
        'session_name': session_name,
        'device_id': 'ECASSAVA-001',
        'condition': cls,
        'mq2_mean': round(mq2_m, 4),
        'mq3_mean': round(mq3_m, 4),
        'mq135_mean': round(mq135_m, 4),
        'mq2_std': round(float(np.random.uniform(std_lo, std_hi)), 4),
        'mq3_std': round(float(np.random.uniform(std_lo, std_hi)), 4),
        'mq135_std': round(float(np.random.uniform(std_lo, std_hi)), 4),
        'mq2_min': int(mq2_m - np.random.randint(15, 60)),
        'mq3_min': int(mq3_m - np.random.randint(15, 60)),
        'mq135_min': int(mq135_m - np.random.randint(20, 70)),
        'mq2_max': int(mq2_m + np.random.randint(15, 60)),
        'mq3_max': int(mq3_m + np.random.randint(15, 60)),
        'mq135_max': int(mq135_m + np.random.randint(20, 70)),
        'reading_count': int(np.random.randint(25, 45)),
        'started_at': start_t.strftime('%Y-%m-%d %H:%M:%S'),
        'ended_at': end_t.strftime('%Y-%m-%d %H:%M:%S'),
    }


def generate_dataset(num_samples_per_class=50):
    """Pusat kelas DIKALIBRASI ke hardware riil (jangkar DB):
    fresh ≈ (400, 900, 400), stale ≈ (2100, 1650, 1950),
    rotten di atas stale mengikuti pola alat (mis. 2850, 2300, 2650).
    Zona transisi di antaranya membuat batas landai (probabilitas
    terkalibrasi), tidak berupa cliff yang bikin label goyang."""
    records = []
    base_time = datetime.now() - timedelta(days=10)
    session_id = 1

    def add(cls, tag, n, c2, s2, c3, s3, c5, s5):
        nonlocal session_id
        for i in range(n):
            start_t = base_time + timedelta(hours=session_id * 2)
            records.append(_row(
                session_id, f"Sample_{cls.capitalize()}_{tag}_{i+1:02d}", cls,
                np.random.normal(c2, s2), np.random.normal(c3, s3),
                np.random.normal(c5, s5), start_t,
            ))
            session_id += 1

    # ── fresh: inti 35 + segar-akhir dekat batas 15 ──
    add('fresh', 'Core', 35, 400, 50, 900, 90, 400, 50)
    add('fresh', 'Late', 15, 550, 70, 1150, 100, 550, 70)
    # ── stale: awal-batas 15 + inti 35 + akhir 10 ──
    add('stale', 'Early', 15, 900, 120, 1350, 130, 900, 120)
    add('stale', 'Core', 35, 2100, 150, 1650, 140, 1950, 150)
    add('stale', 'Late', 10, 2400, 150, 1900, 150, 2200, 160)
    # ── rotten: awal-busuk 10 + inti 40 ──
    add('rotten', 'Early', 10, 2500, 160, 2050, 160, 2350, 170)
    add('rotten', 'Core', 40, 2850, 180, 2300, 170, 2650, 180)

    df = pd.DataFrame(records)
    output_filename = "sample_dataset.csv"
    df.to_csv(output_filename, index=False)
    print(f"[OK] Berhasil membuat {len(df)} sample data sintetis")
    print(df['condition'].value_counts().to_string())
    print(f"File: {output_filename}")

if __name__ == "__main__":
    generate_dataset(num_samples_per_class=50)
