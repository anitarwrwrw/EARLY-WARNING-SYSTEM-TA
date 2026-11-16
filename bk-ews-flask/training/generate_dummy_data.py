"""
training/generate_dummy_data.py
Script untuk membuat data latih dummy (csv) sebelum training model.
Berguna jika belum memiliki data historis nyata dari sekolah.

Jalankan: python training/generate_dummy_data.py
Output:   training/data_historis_siswa.csv
"""

import numpy as np
import pandas as pd
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

np.random.seed(42)
N = 300  # Total jumlah sampel

print(f"Membuat {N} data siswa dummy...")

# Buat distribusi yang tidak seimbang (seperti kondisi nyata sekolah)
# 60% Risiko Rendah, 25% Risiko Sedang, 15% Risiko Tinggi
n_rendah = int(N * 0.60)
n_sedang = int(N * 0.25)
n_tinggi = N - n_rendah - n_sedang

def buat_siswa_rendah(n):
    return pd.DataFrame({
        'rata_rata_nilai':        np.random.normal(85, 6, n).clip(70, 100),
        'tren_nilai':             np.random.normal(1.5, 3, n).clip(-5, 10),
        'jumlah_alpha':           np.random.randint(0, 3, n),
        'total_poin_pelanggaran': np.random.randint(0, 5, n),
        'skor_motivasi':          np.random.randint(18, 26, n),
        'skor_keluarga':          np.random.randint(17, 26, n),
        'skor_keamanan':          np.random.randint(18, 26, n),
        'status_risiko':          'Risiko Rendah',
    })

def buat_siswa_sedang(n):
    return pd.DataFrame({
        'rata_rata_nilai':        np.random.normal(73, 5, n).clip(60, 85),
        'tren_nilai':             np.random.normal(-2, 4, n).clip(-10, 5),
        'jumlah_alpha':           np.random.randint(2, 6, n),
        'total_poin_pelanggaran': np.random.randint(5, 20, n),
        'skor_motivasi':          np.random.randint(12, 18, n),
        'skor_keluarga':          np.random.randint(12, 18, n),
        'skor_keamanan':          np.random.randint(13, 19, n),
        'status_risiko':          'Risiko Sedang',
    })

def buat_siswa_tinggi(n):
    return pd.DataFrame({
        'rata_rata_nilai':        np.random.normal(62, 7, n).clip(40, 75),
        'tren_nilai':             np.random.normal(-7, 4, n).clip(-20, 0),
        'jumlah_alpha':           np.random.randint(5, 15, n),
        'total_poin_pelanggaran': np.random.randint(15, 50, n),
        'skor_motivasi':          np.random.randint(5, 13, n),
        'skor_keluarga':          np.random.randint(5, 13, n),
        'skor_keamanan':          np.random.randint(5, 14, n),
        'status_risiko':          'Risiko Tinggi',
    })

df = pd.concat([
    buat_siswa_rendah(n_rendah),
    buat_siswa_sedang(n_sedang),
    buat_siswa_tinggi(n_tinggi),
], ignore_index=True).sample(frac=1, random_state=42)  # Acak urutan

# Bulatkan nilai
df['rata_rata_nilai'] = df['rata_rata_nilai'].round(1)
df['tren_nilai']      = df['tren_nilai'].round(1)

output_path = os.path.join(os.path.dirname(__file__), 'data_historis_siswa.csv')
df.to_csv(output_path, index=False)

print(f"\n[✓] Data berhasil dibuat: {output_path}")
print(f"    Total: {len(df)} baris")
print(f"    Distribusi:\n{df['status_risiko'].value_counts().to_string()}")
print(f"\nLangkah selanjutnya: jalankan python training/train_models.py")

