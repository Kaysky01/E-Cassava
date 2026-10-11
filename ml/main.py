"""
E-CASSAVA Machine Learning – Python FastAPI Service (SVM)
==========================================================
Menerima array pembacaan sensor (window), mengekstrak 12 fitur
statistik secara otomatis di sisi Python, lalu mengklasifikasikan
kondisi singkong menggunakan model SVM (Pipeline: StandardScaler + SVC).

Jalankan:
    pip install -r requirements.txt
    uvicorn main:app --reload --port 8001

Endpoint:
    GET  /health     -> status service dan model SVM
    GET  /model-info -> metadata model SVM
    POST /predict    -> inferensi real-time dari array window sensor
"""

import json
import logging
from pathlib import Path
from typing import Any, Dict, List, Optional, Union

import joblib
import numpy as np
from fastapi import FastAPI, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

# ─────────────────────────────────────────────
# Setup logging
# ─────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
)
logger = logging.getLogger("ecassava-ml-svm")

# ─────────────────────────────────────────────
# FastAPI app
# ─────────────────────────────────────────────
app = FastAPI(
    title="E-CASSAVA SVM ML Service",
    description="Machine Learning prediction service (Support Vector Machine) untuk Electronic Nose Singkong",
    version="2.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ─────────────────────────────────────────────
# Model path & Metadata
# ─────────────────────────────────────────────
MODEL_DIR = Path(__file__).parent / "models"
MODEL_PATH = MODEL_DIR / "ecassava_model.joblib"
META_PATH = MODEL_DIR / "model_meta.json"

FEATURE_COLUMNS = [
    "mq2_mean",   "mq3_mean",   "mq135_mean",
    "mq2_std",    "mq3_std",    "mq135_std",
    "mq2_min",    "mq3_min",    "mq135_min",
    "mq2_max",    "mq3_max",    "mq135_max",
]
CLASSES = ["fresh", "stale", "rotten"]

# ─────────────────────────────────────────────
# Policy keputusan (diselaraskan dengan Laravel: config/ecassava.php)
# ─────────────────────────────────────────────
CONF_THRESHOLD = 0.60    # di bawah ini → uncertain (belum yakin)
MARGIN_THRESHOLD = 0.15  # selisih prob peringkat 1-2 di bawah ini → uncertain
FAULT_RATIO = 0.5        # >50% window mentok 0 / 4095 di satu channel → sensor fault
ADC_MIN = 0
ADC_MAX = 4095

model = None
meta: Dict[str, Any] = {}


def load_model() -> None:
    """Memuat model SVM dan metadata dari disk."""
    global model, meta
    if MODEL_PATH.exists():
        try:
            model = joblib.load(MODEL_PATH)
            if META_PATH.exists():
                with open(META_PATH, "r", encoding="utf-8") as f:
                    meta = json.load(f)
            else:
                meta = {"version": "svm_unversioned", "model_name": "SVM"}
            logger.info("Model SVM berhasil dimuat (Versi: %s)", meta.get("version", "unknown"))
        except Exception as e:
            logger.error("Gagal memuat model SVM: %s", e)
            model = None
    else:
        logger.warning("Model SVM belum ditemukan di %s. Jalankan train.py.", MODEL_PATH)


@app.on_event("startup")
def startup_event() -> None:
    MODEL_DIR.mkdir(parents=True, exist_ok=True)
    load_model()


# ─────────────────────────────────────────────
# Pydantic Schemas
# ─────────────────────────────────────────────
class SensorReadingItem(BaseModel):
    """Satu titik data pembacaan sensor."""
    mq2: float = Field(..., ge=0, le=4095, description="Respons ADC MQ-2 (0-4095)")
    mq3: float = Field(..., ge=0, le=4095, description="Respons ADC MQ-3 (0-4095)")
    mq135: float = Field(..., ge=0, le=4095, description="Respons ADC MQ-135 (0-4095)")


class PredictWindowRequest(BaseModel):
    """Input payload berupa window pembacaan sensor."""
    window: Optional[List[SensorReadingItem]] = Field(
        default=None,
        description="Array titik pembacaan sensor dalam window"
    )
    readings: Optional[List[SensorReadingItem]] = Field(
        default=None,
        description="Alias untuk array window pembacaan sensor"
    )

    # Dukungan backward compatibility untuk input 12 fitur precomputed
    mq2_mean: Optional[float] = None
    mq3_mean: Optional[float] = None
    mq135_mean: Optional[float] = None
    mq2_std: Optional[float] = None
    mq3_std: Optional[float] = None
    mq135_std: Optional[float] = None
    mq2_min: Optional[int] = None
    mq3_min: Optional[int] = None
    mq135_min: Optional[int] = None
    mq2_max: Optional[int] = None
    mq3_max: Optional[int] = None
    mq135_max: Optional[int] = None


class PredictionResponse(BaseModel):
    success: bool
    label: Optional[str] = None
    condition: Optional[str] = None
    confidence: Optional[float] = None
    probabilities: Optional[Dict[str, float]] = None
    model_name: str = "SVM"
    model_version: Optional[str] = None
    features: Optional[Dict[str, Any]] = None
    reading_count: Optional[int] = None
    message: str
    # Policy keputusan: bila uncertain=True, label hanya tebakan terbaik —
    # Laravel menampilkannya sebagai "Belum yakin" dan menahan alert.
    uncertain: bool = False
    margin: Optional[float] = None
    warnings: List[str] = Field(default_factory=list)


# ─────────────────────────────────────────────
# Helper: Ekstraksi 12 Fitur di Sisi Python
# ─────────────────────────────────────────────
def extract_features_from_window(items: List[SensorReadingItem]) -> Dict[str, Any]:
    """Mengekstrak 12 fitur statistik (mean, std, min, max) dari window sensor."""
    mq2_vals = np.array([item.mq2 for item in items], dtype=float)
    mq3_vals = np.array([item.mq3 for item in items], dtype=float)
    mq135_vals = np.array([item.mq135 for item in items], dtype=float)

    n = len(items)
    # Gunakan sample standard deviation (ddof=1) jika n > 1
    ddof = 1 if n > 1 else 0

    return {
        "mq2_mean": round(float(np.mean(mq2_vals)), 4),
        "mq3_mean": round(float(np.mean(mq3_vals)), 4),
        "mq135_mean": round(float(np.mean(mq135_vals)), 4),
        "mq2_std": round(float(np.std(mq2_vals, ddof=ddof)), 4),
        "mq3_std": round(float(np.std(mq3_vals, ddof=ddof)), 4),
        "mq135_std": round(float(np.std(mq135_vals, ddof=ddof)), 4),
        "mq2_min": int(np.min(mq2_vals)),
        "mq3_min": int(np.min(mq3_vals)),
        "mq135_min": int(np.min(mq135_vals)),
        "mq2_max": int(np.max(mq2_vals)),
        "mq3_max": int(np.max(mq3_vals)),
        "mq135_max": int(np.max(mq135_vals)),
    }


def detect_sensor_fault(items: List[SensorReadingItem]) -> List[str]:
    """Deteksi channel yang jenuh (mentok ADC) atau mati dalam sebagian besar window.

    Penyebab umum: AO modul 5V langsung ke ADC ESP32 3.3V (wajib pembagi
    tegangan), sensor belum warm-up, atau kabel putus.
    """
    warnings: List[str] = []
    n = len(items)
    if n == 0:
        return warnings

    channels = {
        "mq2": [item.mq2 for item in items],
        "mq3": [item.mq3 for item in items],
        "mq135": [item.mq135 for item in items],
    }
    for name, vals in channels.items():
        stuck_high = sum(1 for v in vals if v >= ADC_MAX - 5) / n
        stuck_low = sum(1 for v in vals if v <= ADC_MIN + 10) / n
        if stuck_high > FAULT_RATIO:
            warnings.append(f"{name}_saturated")
        elif stuck_low > FAULT_RATIO:
            warnings.append(f"{name}_dead")
    return warnings


# ─────────────────────────────────────────────
# Endpoints
# ─────────────────────────────────────────────
@app.get("/health")
def health_check():
    """Mengembalikan status health service dan info model SVM."""
    return {
        "status": "ok",
        "service": "E-CASSAVA SVM ML Service",
        "model_loaded": model is not None,
        "model_name": meta.get("model_name", "SVM"),
        "model_type": meta.get("algorithm", "Pipeline(StandardScaler + SVC)"),
        "model_version": meta.get("version"),
        "accuracy": meta.get("accuracy"),
        "classes": meta.get("classes", CLASSES),
        "best_params": meta.get("best_params"),
    }


@app.get("/model-info")
def model_info():
    """Mengembalikan metadata lengkap model yang sedang aktif."""
    return {
        "model_loaded": model is not None,
        "model_name": meta.get("model_name", "SVM"),
        "model_version": meta.get("version"),
        "trained_at": meta.get("trained_at"),
        "training_samples": meta.get("training_samples"),
        "test_samples": meta.get("test_samples"),
        "accuracy": meta.get("accuracy"),
        "cv_score": meta.get("cv_score"),
        "best_params": meta.get("best_params"),
        "features": FEATURE_COLUMNS,
        "classes": meta.get("classes", CLASSES),
    }


@app.post("/predict", response_model=PredictionResponse)
def predict(payload: Union[PredictWindowRequest, List[SensorReadingItem]]) -> PredictionResponse:
    """
    Menerima window pembacaan sensor (array N data), mengekstrak 12 fitur,
    lalu memprediksi kondisi singkong menggunakan SVM.
    """
    if model is None:
        raise HTTPException(
            status_code=status.HTTP_503_SERVICE_UNAVAILABLE,
            detail="Model SVM belum dimuat atau belum dilatih. Jalankan train.py.",
        )

    # 1. Parsing input (apakah list langsung atau object PredictWindowRequest)
    readings: Optional[List[SensorReadingItem]] = None
    features_dict: Optional[Dict[str, Any]] = None

    if isinstance(payload, list):
        readings = payload
    else:
        if payload.window is not None:
            readings = payload.window
        elif payload.readings is not None:
            readings = payload.readings
        elif payload.mq2_mean is not None:
            # Mode backward compatibility dengan 12 fitur precomputed
            features_dict = {
                "mq2_mean": payload.mq2_mean,
                "mq3_mean": payload.mq3_mean,
                "mq135_mean": payload.mq135_mean,
                "mq2_std": payload.mq2_std or 0.0,
                "mq3_std": payload.mq3_std or 0.0,
                "mq135_std": payload.mq135_std or 0.0,
                "mq2_min": payload.mq2_min or 0,
                "mq3_min": payload.mq3_min or 0,
                "mq135_min": payload.mq135_min or 0,
                "mq2_max": payload.mq2_max or 0,
                "mq3_max": payload.mq3_max or 0,
                "mq135_max": payload.mq135_max or 0,
            }

    # 2. Ekstraksi fitur jika menerima window
    if readings is not None:
        if len(readings) < 5:
            raise HTTPException(
                status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
                detail=f"Jumlah data sensor dalam window kurang ({len(readings)}). Minimal 5 pembacaan diperlukan.",
            )
        features_dict = extract_features_from_window(readings)

    if not features_dict:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Input tidak valid. Kirim 'window' berisi array pembacaan sensor.",
        )

    # 3. Susun vektor fitur sesuai urutan FEATURE_COLUMNS
    try:
        feature_vector = np.array([[features_dict[col] for col in FEATURE_COLUMNS]], dtype=float)
    except KeyError as e:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=f"Fitur tidak lengkap: {e}",
        )

    # 4. Inferensi SVM (Pipeline otomatis menjalankan StandardScaler -> SVC)
    try:
        predicted_class = str(model.predict(feature_vector)[0])
        probabilities_raw = model.predict_proba(feature_vector)[0]
        confidence = float(np.max(probabilities_raw))

        prob_dict = {
            str(cls): round(float(p), 4)
            for cls, p in zip(model.classes_, probabilities_raw)
        }

        # Pastikan semua kelas standar ada dalam prob_dict
        for cls in CLASSES:
            if cls not in prob_dict:
                prob_dict[cls] = 0.0

    except Exception as e:
        logger.error("Terjadi kesalahan saat inferensi SVM: %s", e)
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail=f"Kesalahan internal inferensi: {e}",
        )

    # 5. Policy keputusan: margin, uncertain, dan sensor fault.
    #    Label di zona batas (confidence/margin rendah) tetap dikembalikan
    #    sebagai tebakan terbaik, tapi ditandai uncertain agar Laravel
    #    menampilkannya sebagai "Belum yakin" dan menahan alert.
    ranked = sorted(prob_dict.values(), reverse=True)
    margin = round(ranked[0] - (ranked[1] if len(ranked) > 1 else 0.0), 4)
    warnings = detect_sensor_fault(readings) if readings else []
    uncertain = (
        confidence < CONF_THRESHOLD
        or margin < MARGIN_THRESHOLD
        or len(warnings) > 0
    )

    logger.info(
        "Prediksi SVM: %s (Confidence: %.2f%%, margin: %.2f, uncertain: %s) | Sampel Window: %d",
        predicted_class,
        confidence * 100,
        margin,
        uncertain,
        len(readings) if readings else 0,
    )

    return PredictionResponse(
        success=True,
        label=predicted_class,
        condition=predicted_class,
        confidence=round(confidence, 4),
        probabilities=prob_dict,
        model_name="SVM",
        model_version=meta.get("version"),
        features=features_dict,
        reading_count=len(readings) if readings else None,
        message="Klasifikasi SVM berhasil"
        + (" (belum yakin — pantau perkembangan)" if uncertain else ""),
        uncertain=uncertain,
        margin=margin,
        warnings=warnings,
    )
