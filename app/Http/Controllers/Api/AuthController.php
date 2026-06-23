<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'organization' => $user->organization,
                'is_super_admin' => $user->isSuperAdmin(),
                'is_admin' => $user->isAdmin(),
            ],
        ]);
    }

    /**
     * Soft-delete the authenticated admin's account along with all of the
     * members they own, then tear down the session. Members do not own other
     * accounts, so they may only delete themselves.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isMember()) {
            return response()->json([
                'message' => 'Members cannot delete their account here. Please contact your administrator.',
            ], 403);
        }

        $deletedMembers = 0;

        DB::transaction(function () use ($user, &$deletedMembers) {
            // Soft-delete every member that belongs to this admin first, then
            // the admin's own account. SoftDeletes keeps the rows in place
            // (deleted_at is set) so nothing is permanently lost.
            $deletedMembers = $user->members()->delete();

            $user->delete();
        });

        // End the current session so the now-deleted user is fully signed out.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Your account and all of its members have been deleted.',
            'deleted_members' => $deletedMembers,
        ]);
    }
}
