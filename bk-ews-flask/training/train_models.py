"""
training/train_models.py
Script pelatihan model Machine Learning EWS BK - SMK Sunan Ampel

CARA PENGGUNAAN:
1. Upload file ini ke Google Colab atau jalankan di Jupyter Notebook.
2. Siapkan file 'data_historis_siswa.csv' dengan kolom sesuai FEATURE_COLUMNS.
3. Jalankan seluruh script. File model_rf.pkl dan model_iforest.pkl akan tersimpan
   di folder 'models/' yang kemudian di-copy ke server Flask.

KOLOM DATA CSV YANG DIBUTUHKAN:
  rata_rata_nilai, tren_nilai, jumlah_alpha, total_poin_pelanggaran,
  skor_motivasi, skor_keluarga, skor_keamanan, status_risiko (target: Rendah/Sedang/Tinggi)
"""

import os
import sys

sys.stdout.reconfigure(encoding='utf-8')
import joblib
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier, IsolationForest
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import (
    classification_report, confusion_matrix, accuracy_score
)
from imblearn.over_sampling import SMOTE

# ─── Konfigurasi ───────────────────────────────────────────────────────────────
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_PATH = os.path.join(SCRIPT_DIR, "data_historis_siswa.csv")
if not os.path.exists(DATA_PATH):
    DATA_PATH = "data_historis_siswa.csv"

MODEL_OUTPUT_DIR = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "models"))
os.makedirs(MODEL_OUTPUT_DIR, exist_ok=True)

FEATURE_COLUMNS = [
    "rata_rata_nilai", "tren_nilai", "jumlah_alpha",
    "total_poin_pelanggaran", "skor_motivasi", "skor_keluarga", "skor_keamanan",
]
TARGET_COLUMN = "status_risiko"

RANDOM_STATE = 42

# ─── 1. Load & Preprocessing Data ─────────────────────────────────────────────
print("=" * 60)
print("FASE 1: LOAD & PREPROCESSING DATA")
print("=" * 60)

df = pd.read_csv(DATA_PATH)
print(f"[✓] Data dimuat: {df.shape[0]} baris, {df.shape[1]} kolom")
print(f"[i] Distribusi kelas awal:\n{df[TARGET_COLUMN].value_counts()}\n")

# Tangani missing values
df[FEATURE_COLUMNS] = df[FEATURE_COLUMNS].fillna(df[FEATURE_COLUMNS].median())

# Encode label target: Rendah=0, Sedang=1, Tinggi=2
le = LabelEncoder()
le.classes_ = np.array(["Risiko Rendah", "Risiko Sedang", "Risiko Tinggi"])
df["label_encoded"] = le.transform(df[TARGET_COLUMN])

X = df[FEATURE_COLUMNS].values
y = df["label_encoded"].values

# ─── 2. Train-Test Split (80:20) ───────────────────────────────────────────────
print("=" * 60)
print("FASE 2: TRAIN-TEST SPLIT (80% Train : 20% Test)")
print("=" * 60)

X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=RANDOM_STATE, stratify=y
)
print(f"[✓] Data latih: {X_train.shape[0]} sampel")
print(f"[✓] Data uji  : {X_test.shape[0]} sampel\n")

# ─── 3A. SMOTE pada Data Latih ─────────────────────────────────────────────────
print("=" * 60)
print("FASE 3A: SMOTE OVERSAMPLING (Hanya pada Data Latih)")
print("=" * 60)

print(f"[i] Sebelum SMOTE: {dict(zip(*np.unique(y_train, return_counts=True)))}")
smote = SMOTE(random_state=RANDOM_STATE, k_neighbors=min(5, sum(y_train == 2) - 1))
X_train_smote, y_train_smote = smote.fit_resample(X_train, y_train)
print(f"[✓] Setelah SMOTE: {dict(zip(*np.unique(y_train_smote, return_counts=True)))}\n")

# ─── 4A. Pelatihan Random Forest ───────────────────────────────────────────────
print("=" * 60)
print("FASE 4A: PELATIHAN RANDOM FOREST (Supervised)")
print("=" * 60)

rf_model = RandomForestClassifier(
    n_estimators=200,
    max_depth=None,
    min_samples_split=2,
    min_samples_leaf=1,
    class_weight="balanced",
    random_state=RANDOM_STATE,
    n_jobs=-1,
)
rf_model.fit(X_train_smote, y_train_smote)
print("[✓] Model Random Forest berhasil dilatih.")

# ─── 5A. Evaluasi Random Forest ────────────────────────────────────────────────
print("\n" + "=" * 60)
print("FASE 5A: EVALUASI RANDOM FOREST (pada Data Uji 20%)")
print("=" * 60)

y_pred_rf = rf_model.predict(X_test)
target_names = ["Risiko Rendah", "Risiko Sedang", "Risiko Tinggi"]

print(f"\nAccuracy  : {accuracy_score(y_test, y_pred_rf):.4f}")
print(f"\nClassification Report:\n")
print(classification_report(y_test, y_pred_rf, target_names=target_names))
print(f"\nConfusion Matrix:\n{confusion_matrix(y_test, y_pred_rf)}\n")

# Feature Importance
print("Feature Importance (Random Forest):")
fi = sorted(
    zip(FEATURE_COLUMNS, rf_model.feature_importances_),
    key=lambda x: x[1], reverse=True
)
for feat, imp in fi:
    bar = "█" * int(imp * 50)
    print(f"  {feat:<30} {imp:.4f}  {bar}")

# ─── 3B & 4B. Isolation Forest ─────────────────────────────────────────────────
print("\n" + "=" * 60)
print("FASE 3B-4B: PELATIHAN ISOLATION FOREST (Unsupervised)")
print("=" * 60)

# contamination = estimasi proporsi anomali (siswa bermasalah) di populasi
# Sesuaikan nilai ini berdasarkan data historis sekolah (estimasi awal: 10%)
CONTAMINATION = 0.10

iforest_model = IsolationForest(
    n_estimators=200,
    contamination=CONTAMINATION,
    max_samples="auto",
    random_state=RANDOM_STATE,
    n_jobs=-1,
)
iforest_model.fit(X_train)  # Tanpa label, tanpa SMOTE
print(f"[✓] Model Isolation Forest dilatih dengan contamination={CONTAMINATION}")

# Evaluasi Isolation Forest dengan validasi manual Guru BK
# (Asumsikan siswa dengan label 'Risiko Tinggi' = anomali untuk baseline evaluation)
y_iforest_pred = iforest_model.predict(X_test)  # -1 = anomali, 1 = normal
y_iforest_binary = (y_iforest_pred == -1).astype(int)   # 1 = anomali
y_test_binary = (y_test == 2).astype(int)               # 1 = Risiko Tinggi

print(f"\nEvaluasi Isolation Forest (baseline: Risiko Tinggi = Anomali):")
print(f"  Total Anomali Terdeteksi    : {y_iforest_binary.sum()}")
print(f"  Total Risiko Tinggi (actual): {y_test_binary.sum()}")
print(f"\n  Confusion Matrix:\n{confusion_matrix(y_test_binary, y_iforest_binary)}\n")
print(f"  Classification Report:\n")
print(classification_report(y_test_binary, y_iforest_binary,
                             target_names=["Normal", "Anomali"]))

# ─── 6. Export Model ke File .pkl ──────────────────────────────────────────────
print("=" * 60)
print("FASE 6: EXPORT MODEL KE FILE .pkl")
print("=" * 60)

rf_path = os.path.join(MODEL_OUTPUT_DIR, "model_rf.pkl")
if_path = os.path.join(MODEL_OUTPUT_DIR, "model_iforest.pkl")

joblib.dump(rf_model, rf_path)
joblib.dump(iforest_model, if_path)

print(f"[✓] model_rf.pkl     tersimpan di: {rf_path}")
print(f"[✓] model_iforest.pkl tersimpan di: {if_path}")
print("\n✅ SELESAI! Copy kedua file .pkl ke folder 'models/' di server Flask.\n")

