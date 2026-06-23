<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HostProfileController extends Controller
{
    /**
     * Return the host/organizer profile for the current user's tenant.
     * Members see (read-only) their parent admin's host; admins see their own.
     */
    public function show(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant();

        return response()->json([
            'host_name_en' => $tenant->host_name_en,
            'host_name_gu' => $tenant->host_name_gu,
            'image_url'    => $tenant->host_image_url,
            'can_edit'     => $request->user()->isAdmin() || $request->user()->isSuperAdmin(),
        ]);
    }

    /**
     * Upsert the host name/image for the current admin. Only admins and super
     * admins may edit; members inherit and cannot change it.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isSuperAdmin())) {
            return response()->json(['message' => 'You are not allowed to edit the host profile.'], 403);
        }

        $request->validate([
            'host_name_en' => 'nullable|string|max:255',
            'host_name_gu' => 'nullable|string|max:255',
            'image'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'remove'       => 'nullable|boolean',
        ]);

        // Explicit removal (and any replacement) cleans up the old file so we
        // don't orphan images in storage.
        if (($request->boolean('remove') || $request->hasFile('image')) && $user->host_image_path) {
            Storage::disk('public')->delete($user->host_image_path);
            $user->host_image_path = null;
        }

        if ($request->hasFile('image')) {
            $user->host_image_path = $request->file('image')->store('host-images', 'public');
        }

        $user->host_name_en = $request->input('host_name_en') ?: null;
        $user->host_name_gu = $request->input('host_name_gu') ?: null;
        $user->save();

        return $this->show($request);
    }
}
