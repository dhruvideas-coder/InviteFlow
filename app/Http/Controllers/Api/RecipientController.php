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

        $baseQuery = Recipient::with([
            'invitationLinks' => fn ($q) => $q->latest()->with(['document', 'createdBy']),
        ])
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
                $l->where('document_id', '=', $documentId);
            });
        })->where('sent', '=', true)->count();

        $totalPending = Recipient::when($documentId, function ($q) use ($documentId) {
            $q->whereHas('invitationLinks', function ($l) use ($documentId) {
                $l->where('document_id', '=', $documentId);
            });
        })->where('sent', '=', false)->count();

        return response()->json(array_merge($paginated->toArray(), [
            'stats' => [
                'total_sent'    => $totalSent,
                'total_pending' => $totalPending,
            ],
        ]));
    }

    private function normalizeMobile(Request $request): void
    {
        $digits = preg_replace('/\D/', '', $request->input('mobile', ''));
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        $request->merge(['mobile' => '+91' . $digits]);
    }

    public function store(Request $request)
    {
        $this->normalizeMobile($request);

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'mobile' => ['required', 'regex:/^\+91[6-9]\d{9}$/', 'unique:recipients,mobile'],
            'village_en' => 'nullable|string|max:255',
            'village_gu' => 'nullable|string|max:255',
        ]);

        return Recipient::create($validated);
    }

    public function update(Request $request, Recipient $recipient)
    {
        $this->normalizeMobile($request);

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_gu' => 'nullable|string|max:255',
            'mobile' => ['required', 'regex:/^\+91[6-9]\d{9}$/', "unique:recipients,mobile,{$recipient->id}"],
            'village_en' => 'nullable|string|max:255',
            'village_gu' => 'nullable|string|max:255',
            'sent' => 'nullable|boolean',
        ]);

        $recipient->update($validated);

        return $recipient;
    }

    public function destroy(Recipient $recipient)
    {
        $recipient->deleteOrFail();
        return response()->json(['message' => 'Recipient deleted successfully']);
    }

    public function markAsSent(Recipient $recipient)
    {
        $recipient->update(['sent' => true]);
        return $recipient;
    }
}
