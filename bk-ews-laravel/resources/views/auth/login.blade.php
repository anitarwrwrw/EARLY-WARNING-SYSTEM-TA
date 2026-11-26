<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EWS BK SMK Sunan Ampel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body { padding: 2rem; }
        .form-control:focus {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 .2rem rgba(29,78,216,.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border: none;
            color: white;
            padding: .75rem;
            border-radius: .5rem;
            font-weight: 600;
            transition: opacity .15s;
        }
        .btn-login:hover { opacity: .9; color: white; }
    </style>
</head>
<body>
<div class="login-card card mx-3">
    <div class="login-header">
        <div class="mb-3">
            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width:64px;height:64px">
                <span class="text-primary fw-bold" style="font-size:1.5rem">BK</span>
            </div>
        </div>
        <h5 class="fw-bold mb-1">Sistem EWS Bimbingan Konseling</h5>
        <small class="opacity-75">SMK Sunan Ampel, Jombang</small>
    </div>

    <div class="login-body">
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            @if($errors->any())
                <div class="alert alert-danger alert-sm py-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-medium">Username / NIS</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="username"
                           class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}"
                           placeholder="Masukkan username atau NIS"
                           autofocus required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password"
                           class="form-control"
                           placeholder="Masukkan password"
                           required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <hr class="my-3">
        <div class="text-center mb-2">
            <a href="{{ route('kuesioner.publik') }}" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-clipboard-heart me-1"></i>Siswa? Isi Kuesioner Publik (Tanpa Login)
            </a>
        </div>
        <small class="text-muted d-block text-center">
            <strong>Demo Login:</strong><br>
            Admin: <code>admin</code> / <code>password</code><br>
            Guru BK: <code>gurubk</code> / <code>password</code><br>
            Wali Kelas: <code>wali_xi_tkj1</code> / <code>password</code>
        </small>
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
</script>
</body>
</html>

