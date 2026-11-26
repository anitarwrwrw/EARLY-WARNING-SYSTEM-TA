<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — EWS BK SMK Sunan Ampel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            position: relative;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            /* Mesh Gradient lembut (Lavender + Off-white + Sage Teal) */
            background-color: #dce6e3;
            background-image:
                radial-gradient(at 22% 14%, rgba(191, 178, 232, 0.85) 0px, transparent 52%),
                radial-gradient(at 84% 10%, rgba(246, 247, 249, 0.92) 0px, transparent 50%),
                radial-gradient(at 88% 82%, rgba(92, 152, 141, 0.88) 0px, transparent 56%),
                radial-gradient(at 12% 86%, rgba(114, 168, 158, 0.85) 0px, transparent 54%),
                radial-gradient(at 52% 48%, rgba(235, 240, 241, 0.78) 0px, transparent 60%);
        }

        /* Tekstur film grain halus seperti pada referensi */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0.22;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        /* Brand logo di pojok kiri atas */
        .brand-corner {
            position: fixed;
            top: 1.5rem;
            left: 1.75rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #18181b;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .brand-mark {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #18181b;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
        }

        /* Kartu Glassmorphism Tengah */
        .glass-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 390px;
            padding: 2.35rem 2rem 1.9rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.44);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow:
                0 20px 50px rgba(31, 41, 55, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .login-title {
            font-size: 1.55rem;
            font-weight: 600;
            color: #18181b;
            letter-spacing: -0.02em;
            text-align: center;
            margin-bottom: 0.45rem;
        }

        .login-subtitle {
            font-size: 0.81rem;
            line-height: 1.45;
            color: #52525b;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* Input modern lembut */
        .input-glass-wrap {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .input-glass-icon {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #71717a;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-glass {
            width: 100%;
            height: 44px;
            padding: 0.55rem 2.5rem 0.55rem 2.45rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.75);
            background: rgba(255, 255, 255, 0.74);
            color: #18181b;
            font-size: 0.875rem;
            transition: all 0.18s ease;
        }

        .input-glass::placeholder {
            color: #71717a;
        }

        .input-glass:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(24, 24, 27, 0.28);
            box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.06);
        }

        .btn-eye {
            position: absolute;
            right: 0.55rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #71717a;
            padding: 0.25rem 0.4rem;
            cursor: pointer;
        }

        .btn-eye:hover {
            color: #18181b;
        }

        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.35rem 0 1.05rem;
            font-size: 0.78rem;
            color: #3f3f46;
        }

        .form-check-input:checked {
            background-color: #27272a;
            border-color: #27272a;
        }

        /* Tombol Utama Dark Charcoal seperti referensi */
        .btn-charcoal {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(180deg, #3f4145 0%, #242528 100%);
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 500;
            box-shadow: 0 6px 16px rgba(24, 24, 27, 0.22);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .btn-charcoal:hover {
            filter: brightness(1.08);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-charcoal:active {
            transform: translateY(0);
        }

        /* Pemisah "atau akses siswa" */
        .divider-text {
            display: flex;
            align-items: center;
            text-align: center;
            color: #52525b;
            font-size: 0.76rem;
            margin: 1.2rem 0 0.85rem;
        }

        .divider-text::before,
        .divider-text::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid rgba(24, 24, 27, 0.12);
        }

        .divider-text::before { margin-right: 0.75rem; }
        .divider-text::after { margin-left: 0.75rem; }

        /* Tombol putih lembut untuk Siswa / Quick Demo */
        .btn-soft-white {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            width: 100%;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.85);
            color: #18181b;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: background 0.15s ease, transform 0.15s ease;
        }

        .btn-soft-white:hover {
            background: rgba(255, 255, 255, 0.96);
            color: #18181b;
            transform: translateY(-1px);
        }

        /* Kartu kecil pill untuk Demo Login cepat */
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            margin-top: 0.65rem;
        }

        .btn-demo-pill {
            height: 36px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.8);
            color: #27272a;
            font-size: 0.74rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.12s ease;
        }

        .btn-demo-pill:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px);
        }

        .alert-glass {
            background: rgba(254, 226, 226, 0.85);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #991b1b;
            border-radius: 10px;
            font-size: 0.8rem;
            padding: 0.6rem 0.85rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .brand-corner {
                position: static;
                margin-bottom: 1.25rem;
            }
        }
    </style>
</head>
<body>

{{-- Logo Pojok Kiri Atas --}}
<a href="{{ route('login') }}" class="brand-corner">
    <span class="brand-mark">BK</span>
    <span>EWS Sunan Ampel</span>
</a>

{{-- Kartu Login Glassmorphism --}}
<div class="glass-card">
    <h1 class="login-title">Selamat datang!</h1>
    <p class="login-subtitle">
        Sistem Peringatan Dini Bimbingan Konseling<br>
        SMK Sunan Ampel, Jombang
    </p>

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        @if($errors->any())
            <div class="alert-glass" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Username --}}
        <div class="input-glass-wrap">
            <i class="bi bi-person input-glass-icon"></i>
            <input type="text"
                   name="username"
                   id="username"
                   class="input-glass @error('username') is-invalid @enderror"
                   value="{{ old('username') }}"
                   placeholder="Username"
                   autofocus
                   required>
        </div>

        {{-- Password --}}
        <div class="input-glass-wrap">
            <i class="bi bi-lock input-glass-icon"></i>
            <input type="password"
                   name="password"
                   id="password"
                   class="input-glass"
                   placeholder="Password"
                   required>
            <button type="button" class="btn-eye" id="togglePassword" aria-label="Lihat Password">
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>

        {{-- Opsi Ingat Saya --}}
        <div class="options-row">
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <span class="text-muted">Portal Pengelola</span>
        </div>

        {{-- Tombol Masuk --}}
        <button type="submit" class="btn-charcoal">
            Masuk
        </button>
    </form>

    {{-- Pemisah ke Kuesioner Publik Siswa --}}
    <div class="divider-text">atau untuk siswa</div>

    <a href="{{ route('kuesioner.publik') }}" class="btn-soft-white">
        <i class="bi bi-clipboard2-heart"></i>
        <span>Isi Kuesioner Siswa (Tanpa Login)</span>
    </a>

    {{-- Demo Login Cepat (klik untuk isi otomatis) --}}
    <div class="divider-text">akun demo pengelola</div>
    <div class="demo-grid">
        <button type="button" class="btn-demo-pill" onclick="fillDemo('admin', 'password')" title="Isi akun Admin">
            <i class="bi bi-shield-lock"></i> Admin
        </button>
        <button type="button" class="btn-demo-pill" onclick="fillDemo('gurubk', 'password')" title="Isi akun Guru BK">
            <i class="bi bi-heart-pulse"></i> Guru BK
        </button>
        <button type="button" class="btn-demo-pill" onclick="fillDemo('wali_xi_tkj1', 'password')" title="Isi akun Wali Kelas">
            <i class="bi bi-person-workspace"></i> Wali
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            pwd.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    function fillDemo(username, password) {
        document.getElementById('username').value = username;
        document.getElementById('password').value = password;
        document.getElementById('username').focus();
    }
</script>
</body>
</html>
