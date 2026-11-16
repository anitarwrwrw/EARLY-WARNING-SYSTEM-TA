"""
app.py - Flask Machine Learning Service untuk EWS Bimbingan Konseling
SMK Sunan Ampel, Jombang

Menangani endpoint prediksi menggunakan:
  - Random Forest (Supervised Classification)
  - Isolation Forest (Unsupervised Anomaly Detection)
"""

import os
import sys

sys.stdout.reconfigure(encoding='utf-8')
import joblib
import numpy as np
import pandas as pd
from flask import Flask, request, jsonify
from flask_cors import CORS
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
CORS(app)

# ─── Muat Model Pre-Trained ────────────────────────────────────────────────────
MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
RF_MODEL_PATH = os.path.join(MODEL_DIR, "model_rf.pkl")
IF_MODEL_PATH = os.path.join(MODEL_DIR, "model_iforest.pkl")

model_rf = None
model_iforest = None

def load_models():
    """Muat kedua model .pkl saat server Flask pertama kali dijalankan."""
    global model_rf, model_iforest
    try:
        if os.path.exists(RF_MODEL_PATH):
            model_rf = joblib.load(RF_MODEL_PATH)
            print(f"[✓] Model Random Forest berhasil dimuat dari: {RF_MODEL_PATH}")
        else:
            print(f"[!] PERINGATAN: File model_rf.pkl tidak ditemukan di '{MODEL_DIR}'.")
            print("    Jalankan script training/train_models.py terlebih dahulu.")

        if os.path.exists(IF_MODEL_PATH):
            model_iforest = joblib.load(IF_MODEL_PATH)
            print(f"[✓] Model Isolation Forest berhasil dimuat dari: {IF_MODEL_PATH}")
        else:
            print(f"[!] PERINGATAN: File model_iforest.pkl tidak ditemukan di '{MODEL_DIR}'.")
    except Exception as e:
        print(f"[✗] Gagal memuat model: {e}")

load_models()

# ─── Konfigurasi Fitur ─────────────────────────────────────────────────────────
FEATURE_COLUMNS = [
    "rata_nilai",
    "tren_nilai",
    "jumlah_alpha",
    "poin_pelanggaran",
    "skor_motivasi",
    "skor_keluarga",
    "skor_keamanan",
]

# Pemetaan alias kolom dari input request
FEATURE_ALIASES = {
    "rata_nilai": ["rata_nilai", "rata_rata_nilai"],
    "tren_nilai": ["tren_nilai"],
    "jumlah_alpha": ["jumlah_alpha"],
    "poin_pelanggaran": ["poin_pelanggaran", "total_poin_pelanggaran"],
    "skor_motivasi": ["skor_motivasi"],
    "skor_keluarga": ["skor_keluarga"],
    "skor_keamanan": ["skor_keamanan"],
}

# Mapping label kelas dari integer ke string
RISK_LABEL_MAP = {0: "Risiko Rendah", 1: "Risiko Sedang", 2: "Risiko Tinggi"}

# ─── Fungsi Helper ─────────────────────────────────────────────────────────────

def determine_priority_level(status_risiko: str, is_anomali: bool) -> tuple:
    """
    Menentukan level prioritas berdasarkan Decision Matrix dari blueprint.

    Returns:
        (level_prioritas: int, label_prioritas: str)
    """
    matrix = {
        ("Risiko Tinggi", True):   (1, "Prioritas 1 (Darurat / Kritis)"),
        ("Risiko Tinggi", False):  (2, "Prioritas 2 (Tindak Lanjut Segera)"),
        ("Risiko Sedang", True):   (3, "Prioritas 3 (Pantau Ketat - Perubahan Mendadak)"),
        ("Risiko Rendah", True):   (3, "Prioritas 3 (Pantau Ketat - Perubahan Mendadak)"),
        ("Risiko Sedang", False):  (4, "Prioritas 4 (Pembinaan Berkala)"),
        ("Risiko Rendah", False):  (5, "Aman (Kondisi Kondusif)"),
    }
    return matrix.get((status_risiko, is_anomali), (5, "Aman (Kondisi Kondusif)"))


def extract_pemicu_utama(row: dict, status_risiko: str, is_anomali: bool) -> str:
    """
    Menghasilkan kalimat penjelasan pemicu risiko berdasarkan aturan threshold.
    Fitur Explainability sederhana tanpa memerlukan library SHAP/LIME.
    """
    triggers = []

    tren_val = row.get("tren_nilai", 0)
    alpha_val = row.get("jumlah_alpha", 0)
    nilai_val = row.get("rata_nilai", row.get("rata_rata_nilai", 100))
    poin_val = row.get("poin_pelanggaran", row.get("total_poin_pelanggaran", 0))

    if tren_val <= -5:
        triggers.append(f"Tren Nilai Turun ({tren_val:+.1f})")
    if alpha_val >= 4:
        triggers.append(f"Alpha Tinggi ({alpha_val} hari)")
    if nilai_val < 70:
        triggers.append(f"Nilai Rata-rata Rendah ({nilai_val:.1f})")
    if poin_val >= 15:
        triggers.append(f"Poin Pelanggaran Tinggi ({poin_val})")
    if row.get("skor_motivasi", 25) <= 12:
        triggers.append("Motivasi Belajar Rendah")
    if row.get("skor_keluarga", 25) <= 12:
        triggers.append("Dukungan Keluarga Rendah")
    if row.get("skor_keamanan", 25) <= 12:
        triggers.append("Rasa Aman di Sekolah Rendah")

    if not triggers:
        if status_risiko == "Risiko Rendah" and not is_anomali:
            return "Tidak ada indikator berisiko"
        return "Kombinasi beberapa faktor terdeteksi oleh model"

    return ", ".join(triggers)


# ─── Endpoint Utama ────────────────────────────────────────────────────────────

@app.route("/", methods=["GET"])
def index():
    """Health check endpoint."""
    return jsonify({
        "service": "EWS BK - Flask ML Service",
        "sekolah": "SMK Sunan Ampel, Jombang",
        "status": "online",
        "model_rf_loaded": model_rf is not None,
        "model_iforest_loaded": model_iforest is not None,
    })


@app.route("/api/predict-ews", methods=["POST"])
def predict_ews():
    """
    Endpoint utama: menerima data siswa dari Laravel dan mengembalikan hasil prediksi EWS.

    Request Body (JSON):
    {
        "semester": "Ganjil 2026/2027",
        "data_siswa": [
            {
                "siswa_id": 101,
                "rata_rata_nilai": 76.5,
                "tren_nilai": -9.0,
                "jumlah_alpha": 6,
                "total_poin_pelanggaran": 25,
                "skor_motivasi": 11,
                "skor_keluarga": 14,
                "skor_keamanan": 18
            }
        ]
    }
    """
    # Validasi model sudah dimuat
    if model_rf is None or model_iforest is None:
        return jsonify({
            "status": "error",
            "message": "Model machine learning belum tersedia. Hubungi administrator untuk menjalankan script training.",
        }), 503

    # Validasi request JSON
    if not request.is_json:
        return jsonify({"status": "error", "message": "Request harus berformat JSON."}), 400

    payload = request.get_json()
    semester = payload.get("semester", "Tidak diketahui")
    data_siswa = payload.get("data_siswa", [])

    if not data_siswa or not isinstance(data_siswa, list):
        return jsonify({"status": "error", "message": "Field 'data_siswa' wajib diisi dan berupa array."}), 400

    hasil_prediksi = []
    errors = []

    for siswa in data_siswa:
        siswa_id = siswa.get("siswa_id")
        try:
            # Susun fitur dalam urutan yang benar dengan dukungan alias
            features = []
            for col in FEATURE_COLUMNS:
                val = 0
                for alias in FEATURE_ALIASES.get(col, [col]):
                    if alias in siswa:
                        val = siswa[alias]
                        break
                features.append(float(val))

            X = np.array(features).reshape(1, -1)

            # --- Prediksi Random Forest ---
            rf_pred_int = int(model_rf.predict(X)[0])
            status_risiko = RISK_LABEL_MAP.get(rf_pred_int, "Risiko Rendah")

            # Probabilitas per kelas (untuk informasi tambahan)
            rf_proba = model_rf.predict_proba(X)[0].tolist()
            rf_proba_dict = {RISK_LABEL_MAP[i]: round(p, 4) for i, p in enumerate(rf_proba)}

            # --- Deteksi Anomali Isolation Forest ---
            # predict() mengembalikan -1 untuk anomali, 1 untuk normal
            if_pred = int(model_iforest.predict(X)[0])
            is_anomali = if_pred == -1
            anomaly_score = float(model_iforest.decision_function(X)[0])

            # --- Penentuan Level Prioritas ---
            level_prioritas, label_prioritas = determine_priority_level(status_risiko, is_anomali)

            # --- Ekstraksi Pemicu Utama ---
            pemicu_utama = extract_pemicu_utama(siswa, status_risiko, is_anomali)

            hasil_prediksi.append({
                "siswa_id": siswa_id,
                "status_risiko": status_risiko,
                "is_anomali": is_anomali,
                "anomaly_score": round(anomaly_score, 4),
                "rf_probabilitas": rf_proba_dict,
                "level_prioritas": level_prioritas,
                "label_prioritas": label_prioritas,
                "pemicu_utama": pemicu_utama,
            })

        except Exception as e:
            errors.append({"siswa_id": siswa_id, "error": str(e)})

    response = {
        "status": "success" if not errors else "partial",
        "semester": semester,
        "total_diproses": len(hasil_prediksi),
        "total_error": len(errors),
        "hasil_prediksi": hasil_prediksi,
    }
    if errors:
        response["errors"] = errors

    return jsonify(response), 200


@app.route("/health", methods=["GET"])
@app.route("/api/health", methods=["GET"])
def health():
    """Detail health check untuk monitoring."""
    return jsonify({
        "status": "ok",
        "models": {
            "random_forest": "loaded" if model_rf is not None else "not_loaded",
            "isolation_forest": "loaded" if model_iforest is not None else "not_loaded",
        },
        "features": FEATURE_COLUMNS,
    })


# ─── Entry Point ───────────────────────────────────────────────────────────────
if __name__ == "__main__":
    port = int(os.getenv("FLASK_PORT", 5000))
    debug = os.getenv("FLASK_DEBUG", "1") == "1"
    app.run(host="127.0.0.1", port=port, debug=debug)

