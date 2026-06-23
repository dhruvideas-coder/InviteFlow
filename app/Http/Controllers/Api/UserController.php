<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * List soft-deleted accounts (admins and members). Super admin only —
     * this is the recovery/recycle-bin view from which users can be restored
     * or permanently removed.
     */
    public function trashed(Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::onlyTrashed()
            ->with(['parent' => fn ($q) => $q->withTrashed()])
            ->orderBy('deleted_at', 'desc')
            ->get();

        return response()->json($users);
    }

    /**
     * Restore a soft-deleted account back to active. When an admin is restored
     * their members (that were removed together with them) are restored too.
     * Super admin only.
     */
    public function restore(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$user->trashed()) {
            return response()->json(['message' => 'This account is already active.'], 400);
        }

        $user->restore();

        $restoredMembers = 0;
        if ($user->isAdmin()) {
            $restoredMembers = User::onlyTrashed()
                ->where('parent_id', $user->id)
                ->where('role', 'member')
                ->restore();
        }

        return response()->json([
            'message' => 'Account restored successfully',
            'restored_members' => $restoredMembers,
            'user' => $user,
        ]);
    }

    /**
     * Permanently delete a soft-deleted account. When an admin is purged their
     * members are purged with them. Super admin only, and the account must
     * already be soft-deleted (a safety gate against accidental hard deletes).
     */
    public function forceDelete(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$user->trashed()) {
            return response()->json(['message' => 'Only deleted accounts can be permanently removed.'], 400);
        }

        DB::transaction(function () use ($user) {
            // Everyone whose data gets purged: the account itself plus, for an
            // admin, all of the members they own.
            $memberIds = $user->isAdmin()
                ? User::withTrashed()
                    ->where('parent_id', $user->id)
                    ->where('role', 'member')
                    ->pluck('id')
                : collect();

            // Copy before pushing so $memberIds is not mutated.
            $ownerIds = collect($memberIds)->push($user->id);

            // Delete recipients these accounts added FIRST, while their owner
            // id is still set. Each recipient's invitation links cascade away
            // via the recipient_id foreign key.
            Recipient::whereIn('created_by_user_id', $ownerIds)->delete();

            // Then permanently remove the member accounts and the account itself.
            if ($memberIds->isNotEmpty()) {
                User::withTrashed()->whereIn('id', $memberIds)->forceDelete();
            }

            $user->forceDelete();
        });

        return response()->json(['message' => 'Account permanently deleted']);
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
