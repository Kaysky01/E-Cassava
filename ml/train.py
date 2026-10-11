"""
E-CASSAVA – SVM Training Script
================================
Melatih model Support Vector Machine (SVM) menggunakan Pipeline:
StandardScaler -> SVC(probability=True) dengan GridSearchCV untuk
tuning hyperparameter (kernel, C, gamma).

Dataset input: CSV yang diexport dari Laravel (/sessions/export-csv)
atau dataset sampel (generate_sample_data.py).

Cara pakai:
    python train.py --csv path/to/dataset.csv

Output:
    models/ecassava_model.joblib  <- Pipeline model tersimpan
    models/model_meta.json        <- Metadata model (akurasi, best params, dsb)
"""

import argparse
import json
import warnings
from datetime import datetime
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
from sklearn.metrics import classification_report, confusion_matrix
from sklearn.model_selection import GridSearchCV, StratifiedKFold, train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import StandardScaler
from sklearn.svm import SVC

warnings.filterwarnings("ignore")

# Urutan 12 fitur harus konsisten antara training dan inference
FEATURE_COLUMNS = [
    "mq2_mean",   "mq3_mean",   "mq135_mean",
    "mq2_std",    "mq3_std",    "mq135_std",
    "mq2_min",    "mq3_min",    "mq135_min",
    "mq2_max",    "mq3_max",    "mq135_max",
]
TARGET_COLUMN = "condition"
CLASSES = ["fresh", "stale", "rotten"]
MODEL_DIR = Path(__file__).parent / "models"


def train(csv_path: str) -> None:
    print("=" * 65)
    print("  E-CASSAVA Machine Learning – SVM Model Training")
    print("=" * 65)

    # ── 1. Load Dataset ──────────────────────────────────────────
    df = pd.read_csv(csv_path)
    print(f"\n[1/5] Membaca data: {len(df)} sesi dari '{csv_path}'")

    missing = [c for c in FEATURE_COLUMNS + [TARGET_COLUMN] if c not in df.columns]
    if missing:
        raise ValueError(f"Kolom wajib tidak ditemukan dalam CSV: {missing}")

    # Filter hanya sesi yang memiliki label valid dan data pembacaan cukup
    df = df[df[TARGET_COLUMN].isin(CLASSES)]
    if "reading_count" in df.columns:
        df = df[df["reading_count"] >= 5]

    print(f"      Sesi valid untuk training: {len(df)}")
    class_counts = df[TARGET_COLUMN].value_counts()
    print("      Distribusi kelas:")
    for cls in CLASSES:
        cnt = class_counts.get(cls, 0)
        print(f"        - {cls:<8}: {cnt} sampel")

    if len(df) < 6:
        raise ValueError("Data training terlalu sedikit (minimal 6 sampel dengan perwakilan kelas).")

    min_class_samples = class_counts.min()

    # ── 2. Split Dataset (Train & Test) ──────────────────────────
    X = df[FEATURE_COLUMNS].values
    y = df[TARGET_COLUMN].values

    stratify_split = y if min_class_samples >= 2 else None
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=stratify_split
    )
    print(f"\n[2/5] Pembagian data: Train = {len(X_train)}, Test = {len(X_test)}")

    # ── 3. Pipeline: StandardScaler + SVC ────────────────────────
    print("\n[3/5] Membangun Pipeline & Menjalankan GridSearchCV...")
    pipeline = Pipeline([
        ("scaler", StandardScaler()),
        ("svm", SVC(probability=True, random_state=42, class_weight="balanced"))
    ])

    param_grid = [
        {
            "svm__kernel": ["rbf"],
            "svm__C": [0.1, 1, 10, 50, 100],
            "svm__gamma": ["scale", "auto", 0.01, 0.1, 1.0],
        },
        {
            "svm__kernel": ["linear"],
            "svm__C": [0.1, 1, 10, 50, 100],
        },
    ]

    # Menyesuaikan n_splits dengan jumlah sampel terkecil di training set
    train_class_counts = pd.Series(y_train).value_counts()
    min_train_samples = int(train_class_counts.min()) if len(train_class_counts) > 0 else 2
    cv_splits = max(2, min(5, min_train_samples))

    cv = StratifiedKFold(n_splits=cv_splits, shuffle=True, random_state=42)

    grid_search = GridSearchCV(
        estimator=pipeline,
        param_grid=param_grid,
        cv=cv,
        scoring="accuracy",
        n_jobs=-1,
        verbose=0,
    )
    grid_search.fit(X_train, y_train)

    best_model = grid_search.best_estimator_
    print(f"      Best Hyperparameters: {grid_search.best_params_}")
    print(f"      Best CV Accuracy:     {grid_search.best_score_ * 100:.2f}%")

    # ── 4. Evaluasi Model pada Data Uji ──────────────────────────
    print("\n[4/5] Evaluasi pada Data Uji (Test Set):")
    y_pred = best_model.predict(X_test)
    accuracy = float((y_pred == y_test).mean())
    print(f"      Test Set Accuracy: {accuracy * 100:.2f}%\n")

    print("Classification Report:")
    print(classification_report(y_test, y_pred, labels=CLASSES, zero_division=0))

    print("Confusion Matrix:")
    cm = confusion_matrix(y_test, y_pred, labels=CLASSES)
    cm_df = pd.DataFrame(cm, index=[f"Actual_{c}" for c in CLASSES], columns=[f"Pred_{c}" for c in CLASSES])
    print(cm_df)

    # ── 5. Simpan Model & Metadata ───────────────────────────────
    MODEL_DIR.mkdir(parents=True, exist_ok=True)
    version = datetime.now().strftime("svm_%Y%m%d_%H%M%S")
    model_file = MODEL_DIR / "ecassava_model.joblib"
    meta_file = MODEL_DIR / "model_meta.json"

    joblib.dump(best_model, model_file)

    # Bersihkan best_params dari prefix 'svm__' untuk keterbacaan
    clean_params = {k.replace("svm__", ""): v for k, v in grid_search.best_params_.items()}

    meta = {
        "model_name": "SVM",
        "algorithm": "Pipeline(StandardScaler + SVC(probability=True))",
        "version": version,
        "trained_at": datetime.now().isoformat(),
        "training_samples": int(len(df)),
        "test_samples": int(len(X_test)),
        "accuracy": round(accuracy, 4),
        "classes": CLASSES,
        "features": FEATURE_COLUMNS,
        "best_params": clean_params,
        "cv_score": round(float(grid_search.best_score_), 4),
    }

    with open(meta_file, "w", encoding="utf-8") as f:
        json.dump(meta, f, indent=2)

    print(f"\n[5/5] Selesai! Model dan metadata berhasil disimpan:")
    print(f"      -> {model_file}")
    print(f"      -> {meta_file}")
    print(f"      Versi: {version} | Akurasi: {accuracy * 100:.2f}%\n")
    print("Silakan restart FastAPI service untuk memuat model SVM:")
    print("      uvicorn main:app --reload --port 8001\n")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="E-CASSAVA SVM Model Training")
    parser.add_argument("--csv", required=True, help="Path ke file dataset CSV")
    args = parser.parse_args()

    train(args.csv)
