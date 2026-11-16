# 🎓 EWS Bimbingan Konseling — SMK Sunan Ampel, Jombang

Aplikasi Web **Early Warning System (EWS) Bimbingan Konseling** berbasis:
- **Laravel 13** (PHP 8.3) — Web utama
- **Flask (Python 3.13)** — ML Service
- **MySQL** — Database
- **Scikit-Learn** — Random Forest + Isolation Forest + SMOTE
- **Fonnte** — WhatsApp Gateway

---

## 📁 Struktur Proyek

```
d:\projectta\
├── bk-ews-laravel/         ← Laravel (Web Utama)
└── bk-ews-flask/           ← Flask (ML Service)
```

---

## 🚀 PANDUAN SETUP LENGKAP

### LANGKAH 1: Buat Database MySQL

Buka **phpMyAdmin** (Laragon → klik tombol Database) dan jalankan:

```sql
CREATE DATABASE ews_bk_smk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### LANGKAH 2: Setup Laravel

```bash
# Masuk ke folder Laravel
cd d:\projectta\bk-ews-laravel

# (Opsional) Sesuaikan .env jika password MySQL bukan kosong
# Edit file .env, ubah DB_PASSWORD=password_mysql_anda

# Jalankan migrasi database (buat semua tabel)
php artisan migrate:fresh

# Isi data dummy untuk demo & pengujian
php artisan db:seed

# Generate app key (jika belum)
php artisan key:generate

# Jalankan server Laravel
php artisan serve
```

✅ Laravel berjalan di: http://localhost:8000

### LANGKAH 3: Setup Flask (ML Service)

```bash
# Masuk ke folder Flask
cd d:\projectta\bk-ews-flask

# Buat virtual environment Python
python -m venv venv

# Aktifkan virtual environment
venv\Scripts\activate        # Windows
# source venv/bin/activate   # Linux/Mac

# Install semua dependencies
pip install -r requirements.txt

# Copy .env (konfigurasi Flask)
copy .env.example .env
```

#### 3a. Latih Model ML (Wajib dilakukan sebelum menjalankan Flask)

```bash
# Buat data latih dummy (opsional, jika belum punya data nyata)
python training/generate_dummy_data.py

# Latih model (menghasilkan model_rf.pkl dan model_iforest.pkl)
python training/train_models.py
```

```bash
# Jalankan server Flask
python app.py
```

✅ Flask berjalan di: http://127.0.0.1:5000

---

## 🔑 Akun Demo (setelah db:seed)

| Role | Username | Password |
|---|---|---|
| **Guru BK** | `gurubk` | `password` |
| **Wali Kelas** | `wali_xi_tkj1` | `password` |
| **Siswa** | `20240001` s/d `20240040` | `password` |

---

## 📋 Konfigurasi WhatsApp (Fonnte)

1. Daftar di https://fonnte.com
2. Hubungkan nomor WhatsApp
3. Salin token API dari dashboard Fonnte
4. Edit file `bk-ews-laravel/.env`:
   ```
   FONNTE_TOKEN=token_anda_di_sini
   ```

---

## 🔗 Alur Penggunaan Sistem

1. **Wali Kelas** login → Input nilai & absensi (manual atau import Excel)
2. **Siswa** login via HP → Isi kuesioner kesejahteraan (15 soal)
3. **Guru BK** login → Pastikan kedua data di atas sudah lengkap
4. **Guru BK** klik **"Jalankan Analisis EWS"** → Sistem menghubungi Flask
5. Dashboard EWS menampilkan pemetaan prioritas 1–5 per siswa
6. **Guru BK** kirim notifikasi WhatsApp ke orang tua (1 klik)
7. **Guru BK** catat tindak lanjut konseling

---

## 🛠️ Perintah Artisan Berguna

```bash
# Refresh migrasi + seed ulang
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear && php artisan config:clear

# Lihat semua route
php artisan route:list
```

---

## 📊 Endpoint Flask ML Service

| Method | URL | Deskripsi |
|---|---|---|
| GET | `/` | Health check |
| POST | `/api/predict-ews` | Prediksi EWS batch siswa |
| GET | `/api/health` | Status model detail |

---

## 📝 Teknologi

| Layer | Teknologi |
|---|---|
| Frontend | Bootstrap 5.3, Chart.js 4, Bootstrap Icons |
| Backend | Laravel 13 (PHP 8.3) |
| ML Service | Flask 3.1, Scikit-Learn 1.6, Pandas |
| Database | MySQL 8+ |
| ML Algorithms | Random Forest, Isolation Forest, SMOTE |
| WhatsApp | Fonnte API |

