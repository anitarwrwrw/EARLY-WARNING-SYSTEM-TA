<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EWS BK - SMK Sunan Ampel Jombang</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:   #1d4ed8;
            --sidebar-w: 260px;
        }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }

        /* ─── Sidebar ─── */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a8a 0%, #1d4ed8 100%);
            position: fixed;
            top: 0; left: 0;
            overflow-y: auto;
            transition: transform .25s ease;
            z-index: 1000;
        }
        #sidebar .sidebar-brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }
        #sidebar .nav-link {
            color: rgba(255,255,255,.75);
            border-radius: .5rem;
            margin: 2px .75rem;
            padding: .55rem .85rem;
            font-size: .875rem;
            transition: background .15s, color .15s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255,255,255,.18);
            color: #fff;
        }
        #sidebar .nav-link .bi { width: 1.4em; }
        #sidebar .nav-section {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.4);
            padding: 1rem 1.5rem .3rem;
        }

        /* ─── Main Content ─── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
        }

        /* ─── Cards ─── */
        .stat-card {
            border: none;
            border-radius: 1rem;
            transition: box-shadow .2s;
        }
        .stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); }

        /* ─── Priority Badge ─── */
        .badge-p1 { background: #ef4444; animation: pulse 1.2s infinite; }
        .badge-p2 { background: #ef4444; }
        .badge-p3 { background: #f97316; }
        .badge-p4 { background: #eab308; color: #1e293b; }
        .badge-p5 { background: #22c55e; }
        @keyframes pulse {
            0%,100% { opacity: 1; }
            50%      { opacity: .6; }
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- ═══════════ SIDEBAR ═══════════ --}}
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                <span class="text-primary fw-bold" style="font-size:1.1rem">BK</span>
            </div>
            <div>
                <div class="text-white fw-bold" style="font-size:.875rem; line-height:1.2">SMK Sunan Ampel</div>
                <div class="text-white-50" style="font-size:.7rem">Sistem EWS Bimbingan Konseling</div>
            </div>
        </div>
    </div>

    @auth
    @php $role = auth()->user()->role; @endphp

    {{-- Menu Admin (Operator) --}}
    @if($role === 'admin')
    <div class="nav-section">Utama</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard Admin
            </a>
        </li>
    </ul>

    <div class="nav-section">Data Master</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.periode.*') ? 'active' : '' }}"
               href="{{ route('admin.periode.index') }}">
                <i class="bi bi-calendar3"></i> Periode Semester
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">
                <i class="bi bi-person-badge"></i> Akun Pengguna
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}"
               href="{{ route('admin.kelas.index') }}">
                <i class="bi bi-building"></i> Data Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}"
               href="{{ route('admin.siswa.index') }}">
                <i class="bi bi-people"></i> Data Siswa
            </a>
        </li>
    </ul>

    <div class="nav-section">Sistem</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}"
               href="{{ route('admin.pengaturan.index') }}">
                <i class="bi bi-gear"></i> Pengaturan API
            </a>
        </li>
    </ul>
    @endif

    {{-- Menu Guru BK --}}
    @if($role === 'guru_bk')
    <div class="nav-section">Dashboard</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.dashboard') ? 'active' : '' }}"
               href="{{ route('bk.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard EWS
            </a>
        </li>
    </ul>

    <div class="nav-section">Data Master</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.master.siswa.*') ? 'active' : '' }}"
               href="{{ route('bk.master.siswa.index') }}">
                <i class="bi bi-people"></i> Data Siswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.master.kelas.*') ? 'active' : '' }}"
               href="{{ route('bk.master.kelas.index') }}">
                <i class="bi bi-building"></i> Data Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.master.pelanggaran.*') ? 'active' : '' }}"
               href="{{ route('bk.master.pelanggaran.index') }}">
                <i class="bi bi-exclamation-triangle"></i> Pelanggaran
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.master.kategori.*') ? 'active' : '' }}"
               href="{{ route('bk.master.kategori.index') }}">
                <i class="bi bi-tags"></i> Kategori Poin
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.kuesioner.*') ? 'active' : '' }}"
               href="{{ route('bk.kuesioner.index') }}">
                <i class="bi bi-ui-checks"></i> Kuesioner Siswa
            </a>
        </li>
    </ul>

    <div class="nav-section">Konseling</div>
    <ul class="nav flex-column px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.tindaklanjut.*') ? 'active' : '' }}"
               href="{{ route('bk.tindaklanjut.index') }}">
                <i class="bi bi-journal-check"></i> Tindak Lanjut BK
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('bk.whatsapp.log') ? 'active' : '' }}"
               href="{{ route('bk.whatsapp.log') }}">
                <i class="bi bi-whatsapp"></i> Log WhatsApp
            </a>
        </li>
    </ul>
    @endif

    {{-- Menu Wali Kelas --}}
    @if($role === 'wali_kelas')
    <ul class="nav flex-column px-2 mt-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('wali.dashboard') ? 'active' : '' }}"
               href="{{ route('wali.dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard Kelas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('wali.input-nilai') ? 'active' : '' }}"
               href="{{ route('wali.input-nilai') }}">
                <i class="bi bi-pencil-square"></i> Input Nilai & Absensi
            </a>
        </li>
    </ul>
    @endif

    {{-- Logout --}}
    <div class="mt-4 p-3">
        <div class="border-top border-white-25 pt-2 mb-2">
            <small class="text-white-50 d-block text-truncate">{{ auth()->user()->name }}</small>
            <small class="text-white-50" style="font-size:.7rem">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</small>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100 text-white"
                    style="background:rgba(255,255,255,.15); border:none">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </button>
        </form>
    </div>
    @endauth
</nav>

{{-- ═══════════ MAIN CONTENT ═══════════ --}}
<div id="main-content">
    {{-- Topbar --}}
    <div class="topbar d-flex align-items-center justify-content-between">
        <button class="btn btn-sm d-md-none" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>
        <h6 class="mb-0 text-muted fw-normal">@yield('page-title', 'Dashboard')</h6>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted">{{ now()->isoFormat('dddd, D MMMM Y') }}</small>
        </div>
    </div>

    {{-- Flash Messages --}}
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Page Content --}}
    <div class="container-fluid px-4 py-3">
        @yield('content')
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
    // Sidebar toggle untuk mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('show');
    });
</script>

@stack('scripts')
</body>
</html>

