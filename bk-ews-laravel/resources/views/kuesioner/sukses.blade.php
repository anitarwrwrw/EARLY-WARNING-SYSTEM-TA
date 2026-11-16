<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuesioner Berhasil Terkirim — SMK Sunan Ampel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .success-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">

            <div class="card success-card p-4 p-md-5 bg-white">
                <div class="mb-4">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center"
                         style="width: 90px; height: 90px;">
                        <i class="bi bi-check-circle-fill display-4"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-dark mb-2">Terima Kasih!</h3>
                <h5 class="text-primary fw-semibold mb-3">
                    {{ session('nama_siswa', 'Siswa Hebat') }}
                </h5>

                <p class="text-muted mb-4">
                    Jawaban survei kesejahteraan dan kenyamanan belajarmu untuk semester
                    <strong class="text-dark">{{ session('nama_semester', 'berjalan') }}</strong>
                    telah berhasil disimpan.
                </p>

                <div class="p-3 bg-light rounded-3 mb-4 text-start small border border-light-subtle">
                    <div class="d-flex align-items-center text-success mb-2">
                        <i class="bi bi-shield-check fs-5 me-2"></i>
                        <span class="fw-bold">Data Dijamin Aman & Rahasia</span>
                    </div>
                    <p class="text-muted mb-0">
                        Informasi yang kamu berikan sangat berharga bagi Guru Bimbingan Konseling dan sekolah untuk terus mendampingi dan mendukung proses belajarmu di SMK Sunan Ampel.
                    </p>
                </div>

                <div>
                    <a href="{{ route('kuesioner.publik') }}" class="btn btn-outline-primary px-4 rounded-pill">
                        <i class="bi bi-arrow-clockwise me-1"></i> Kembali ke Form Kuesioner
                    </a>
                </div>
            </div>

            <div class="mt-4 text-muted small">
                &copy; {{ date('Y') }} Tim Bimbingan Konseling &bull; SMK Sunan Ampel Jombang
            </div>

        </div>
    </div>
</div>

</body>
</html>
