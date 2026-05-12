<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $village = $request->input('village');

        $paginated = Recipient::with('invitationLinks.document')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_gu', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('village_en', 'like', "%{$search}%")
                        ->orWhere('village_gu', 'like', "%{$search}%");
                });
            })
            ->when($village, function ($query, $village) {
                $query->where('village_en', $village);
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(array_merge($paginated->toArray(), [
            'stats' => [
                'total_sent' => Recipient::where('sent', true)->count(),
                'total_pending' => Recipient::where('sent', false)->count(),
            ]
        ]));
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
