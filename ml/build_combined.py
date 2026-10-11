"""
E-CASSAVA – Gabung dataset training
====================================
Menggabungkan:
  1. sample_dataset.csv  (sintetis, pusat kelas terkalibrasi hardware)
  2. db_sessions.csv     (sesi berlabel riil dari DB Laravel)
  3. Copy jitter dari tiap baris riil (augmentasi ringan ±2%) agar
     sedikit sampel riil tetap berpengaruh melawan ratusan sintetis.

Cara pakai (dari folder ml/):
    py generate_sample_data.py   # bila pusat kelas berubah
    # export db_sessions.csv dari Laravel (lihat DOKUMENTASI / artisan)
    py build_combined.py
    py train.py --csv combined_training.csv
"""

from pathlib import Path

import numpy as np
import pandas as pd

HERE = Path(__file__).parent
SYN_PATH = HERE / "sample_dataset.csv"
DB_PATH = HERE / "db_sessions.csv"
OUT_PATH = HERE / "combined_training.csv"

AUGMENT_PER_REAL_ROW = 25
JITTER_PCT = 0.02

np.random.seed(42)


def jitter_row(row: pd.Series, tag: str) -> dict:
    out = row.to_dict()
    for col in ("mq2_mean", "mq3_mean", "mq135_mean"):
        out[col] = round(float(np.clip(
            out[col] * np.random.normal(1.0, JITTER_PCT), 100, 4000)), 4)
    for col in ("mq2_min", "mq3_min", "mq135_min",
                "mq2_max", "mq3_max", "mq135_max"):
        out[col] = int(np.clip(
            out[col] * np.random.normal(1.0, JITTER_PCT), 0, 4095))
    out["session_name"] = f"{row['session_name']}_aug_{tag}"
    return out


def main() -> None:
    syn = pd.read_csv(SYN_PATH)
    frames = [syn]
    print(f"[1] Sintetis : {len(syn)} baris")

    if DB_PATH.exists():
        db = pd.read_csv(DB_PATH)
        db = db[db["reading_count"] >= 5]
        print(f"[2] Riil DB  : {len(db)} sesi")
        aug = [jitter_row(row, f"{i:02d}")
               for _, row in db.iterrows()
               for i in range(AUGMENT_PER_REAL_ROW)]
        aug_df = pd.DataFrame(aug)
        print(f"[3] Augment  : {len(aug_df)} baris (±{JITTER_PCT*100:.0f}% jitter)")
        frames += [db, aug_df]
    else:
        print("[2] db_sessions.csv tidak ada — hanya sintetis.")

    combo = pd.concat(frames, ignore_index=True)
    combo.to_csv(OUT_PATH, index=False)
    print(f"[4] Total    : {len(combo)} baris -> {OUT_PATH.name}")
    print(combo["condition"].value_counts().to_string())


if __name__ == "__main__":
    main()
