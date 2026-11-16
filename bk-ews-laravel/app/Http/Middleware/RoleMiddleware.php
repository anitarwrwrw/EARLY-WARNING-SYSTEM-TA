<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan role pengguna.
 *
 * Role yang didukung:
 * - admin       (Operator / Tata Usaha)
 * - guru_bk     (Guru Bimbingan Konseling)
 * - wali_kelas  (Wali Kelas)
 *
 * Penggunaan di routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:guru_bk')
 *   ->middleware('role:wali_kelas')
 *   ->middleware('role:admin,guru_bk')  ← multiple roles
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
