<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    public function index(Request $request)
    {
        $perPage    = $request->input('per_page', 10);
        $search     = $request->input('search');
        $village    = $request->input('village');
        $documentId = $request->input('document_id');

        $baseQuery = Recipient::with('invitationLinks.document')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_gu', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('village_en', 'like', "%{$search}%")
                        ->orWhere('village_gu', 'like', "%{$search}%");
                });
            })
            ->when($village, function ($q) use ($village) {
                $q->where('village_en', $village);
            })
            ->when($documentId, function ($q) use ($documentId) {
                $q->whereHas('invitationLinks', function ($l) use ($documentId) {
                    $l->where('document_id', $documentId);
                });
            });

        $paginated = $baseQuery->latest()->paginate($perPage);

        $totalSent    = Recipient::when($documentId, function ($q) use ($documentId) {
            $q->whereHas('invitationLinks', function ($l) use ($documentId) {
                $l->where('document_id', $documentId);
            });
        })->where('sent', true)->count();

        $totalPending = Recipient::when($documentId, function ($q) use ($documentId) {
            $q->whereHas('invitationLinks', function ($l) use ($documentId) {
                $l->where('document_id', $documentId);
            });
        })->where('sent', false)->count();

        return response()->json(array_merge($paginated->toArray(), [
            'stats' => [
                'total_sent'    => $totalSent,
                'total_pending' => $totalPending,
            ],
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
