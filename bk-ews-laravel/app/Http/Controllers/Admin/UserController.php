<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        } else {
            // Tampilkan akun guru_bk dan wali_kelas secara default
            $query->whereIn('role', ['guru_bk', 'wali_kelas', 'admin']);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->with('kelas')->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'role'     => 'required|in:admin,guru_bk,wali_kelas',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'] ?? null,
            'role'      => $validated['role'],
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$validated['name']} ({$validated['role']}) berhasil dibuat.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'     => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => 'required|in:admin,guru_bk,wali_kelas',
            'password'  => 'nullable|string|min:6',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'] ?? null,
            'role'      => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function resetPassword(User $user)
    {
        $defaultPassword = 'password';
        $user->update(['password' => Hash::make($defaultPassword)]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password untuk {$user->name} berhasil di-reset menjadi: {$defaultPassword}");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek jika user menjadi wali kelas
        if ($user->kelas()->exists()) {
            return back()->with('error', 'Akun tidak dapat dihapus karena masih ditugaskan sebagai Wali Kelas. Ubah wali kelas terlebih dahulu.');
        }

        $nama = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$nama} berhasil dihapus.");
    }
}
