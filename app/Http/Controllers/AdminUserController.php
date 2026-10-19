<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users with search and role filter.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        $users = $query->withCount(['channels', 'contents', 'assignedContents'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Update the role of a user.
     * PRD Section 18: Admin cannot demote self if it removes admin access blindly.
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in([User::ROLE_CREATOR, User::ROLE_EDITOR, User::ROLE_ADMIN])],
        ]);

        if ($user->id === Auth::id() && $validated['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Anda tidak dapat menurunkan hak akses administrator pada akun Anda sendiri.');
        }

        $user->role = $validated['role'];
        $user->save();

        return back()->with('success', 'Peran pengguna ' . $user->name . ' berhasil diubah menjadi ' . ucfirst($user->role) . '.');
    }
}
