@extends('layouts.app')
@section('page-title', 'Manajemen Akun Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Akun Pengguna Sistem</h4>
        <small class="text-muted">Kelola akun Admin, Guru BK, dan Wali Kelas</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
        <i class="bi bi-person-plus me-1"></i>Tambah Akun
    </button>
</div>

{{-- Filter & Pencarian --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari nama, username, atau email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="guru_bk" {{ request('role') === 'guru_bk' ? 'selected' : '' }}>Guru BK</option>
                    <option value="wali_kelas" {{ request('role') === 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
            @if(request()->hasAny(['search', 'role']) && (request('search') || request('role')))
            <div class="col-md-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabel Pengguna --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $i => $u)
                    <tr>
                        <td class="text-muted small">{{ $users->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $u->name }}</td>
                        <td><code>{{ $u->username }}</code></td>
                        <td><small class="text-muted">{{ $u->email ?? '-' }}</small></td>
                        <td>
                            @if($u->role === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @elseif($u->role === 'guru_bk')
                                <span class="badge bg-primary">Guru BK</span>
                            @else
                                <span class="badge bg-info text-dark">Wali Kelas</span>
                            @endif
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalEditUser{{ $u->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.users.reset-password', $u->id) }}" class="d-inline"
                                  onsubmit="return confirm('Reset password {{ $u->name }} menjadi default (password)?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset Password">
                                    <i class="bi bi-key"></i>
                                </button>
                            </form>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus akun {{ $u->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Edit User --}}
                    <div class="modal fade" id="modalEditUser{{ $u->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('admin.users.update', $u->id) }}" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold">Edit Akun: {{ $u->name }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                                            <input type="text" name="username" class="form-control" value="{{ $u->username }}" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                                            <select name="role" class="form-select" required>
                                                <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="guru_bk" {{ $u->role === 'guru_bk' ? 'selected' : '' }}>Guru BK</option>
                                                <option value="wali_kelas" {{ $u->role === 'wali_kelas' ? 'selected' : '' }}>Wali Kelas</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $u->email }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                                        <input type="password" name="password" class="form-control" minlength="6">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="aktifUser{{ $u->id }}" {{ $u->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aktifUser{{ $u->id }}">Akun Aktif</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada akun pengguna ditemukan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah User --}}
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Tambah Akun Pengguna Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="guru_bk">Guru BK</option>
                            <option value="wali_kelas">Wali Kelas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection
