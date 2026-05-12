<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser->isSuperAdmin()) {
            $users = User::with('parent')->orderBy('created_at', 'desc')->get();
        } elseif ($currentUser->isAdmin()) {
            $users = User::where('parent_id', $currentUser->id)
                ->where('role', 'member')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();

        if ($currentUser->isSuperAdmin()) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => ['required', Rule::in(['super_admin', 'admin', 'member'])],
                'parent_id' => 'nullable|exists:users,id',
            ]);
        } elseif ($currentUser->isAdmin()) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
            ]);
            $validated['role'] = 'member';
            $validated['parent_id'] = $currentUser->id;
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => bcrypt('password123'), // Default password or handle differently
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user has permission to update this user
        if ($currentUser->isSuperAdmin()) {
            // Super admin can update anyone
        } elseif ($currentUser->isAdmin() && $user->parent_id === $currentUser->id && $user->isMember()) {
            // Admin can update their own members
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'Cannot modify your own role here.'], 400);
        }

        if ($currentUser->isSuperAdmin()) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'role' => ['required', Rule::in(['super_admin', 'admin', 'member'])],
                'parent_id' => 'nullable|exists:users,id',
            ]);
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            ]);
            $validated['role'] = 'member'; // Admin cannot change member role
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? $user->role,
            'parent_id' => $validated['parent_id'] ?? $user->parent_id,
        ]);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Check if current user has permission to delete this user
        if ($currentUser->isSuperAdmin()) {
            // Super admin can delete anyone
        } elseif ($currentUser->isAdmin() && $user->parent_id === $currentUser->id && $user->isMember()) {
            // Admin can delete their own members
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->id === $currentUser->id) {
            return response()->json(['message' => 'Cannot delete yourself.'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
