<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index()
    {
        return Recipient::with('invitationLinks.document')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'village_en' => 'nullable|string|max:255',
            'village_gu' => 'nullable|string|max:255',
        ]);

        return Recipient::create($validated);
    }

    public function update(Request $request, Recipient $recipient)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'village_en' => 'nullable|string|max:255',
            'village_gu' => 'nullable|string|max:255',
            'sent' => 'nullable|boolean',
        ]);

        $recipient->update($validated);

        return $recipient;
    }

    public function destroy(Recipient $recipient)
    {
        $recipient->delete();
        return response()->json(['message' => 'Recipient deleted successfully']);
    }

    public function markAsSent(Recipient $recipient)
    {
        $recipient->update(['sent' => true]);
        return $recipient;
    }
}
