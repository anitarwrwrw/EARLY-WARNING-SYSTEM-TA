<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan role pengguna.
 *
 * Penggunaan di routes:
 *   ->middleware('role:bk')
 *   ->middleware('role:wali_kelas')
 *   ->middleware('role:siswa')
 *   ->middleware('role:bk,wali_kelas')  ← multiple roles
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}

