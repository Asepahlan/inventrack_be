<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan hanya admin yang bisa akses
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $query = User::query();
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        return response()->json([
            'success' => true,
            'data' => [
                'users' => $users
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,staff',
        ]);

        User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan.'
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username,' . $user->id,
            'role'     => 'required|in:admin,staff',
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name'     => $request->name,
            'username' => $request->username,
            'role'     => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diperbarui.'
        ]);
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun sendiri.'
            ], 400);
        }

        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ]);
    }
}
