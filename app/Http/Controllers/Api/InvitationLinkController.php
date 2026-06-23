<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationLink;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InvitationLinkController extends Controller
{
    /**
     * Maximum number of WhatsApp invitations a single user may send within
     * any rolling 24-hour window. Other delivery methods (e.g. plain links)
     * are not counted against this cap.
     */
    public const WHATSAPP_DAILY_LIMIT = 50;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InvitationLink::with(['recipient', 'document'])
            ->latest()
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'recipient' => $link->recipient->name_en,
                    'mobile' => $link->recipient->mobile,
                    'document' => $link->document->name,
                    'token' => $link->token,
                    'status' => $link->expires_at && $link->expires_at->isPast() ? 'Expired' : 'Active',
                    'expires' => $link->expires_at ? $link->expires_at->format('M d, Y') : 'Never',
                    'via' => $link->via,
                    'created_at' => $link->created_at->format('M d, Y H:i'),
                ];
            });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:recipients,id',
            'document_id' => 'required|exists:documents,id',
            'via' => 'string',
        ]);

        $user = $request->user();
        $isWhatsApp = ($request->via ?? 'WhatsApp') === 'WhatsApp';

        // Enforce the per-user daily WhatsApp sending limit (rolling 24h).
        if ($isWhatsApp && $user) {
            $quota = $this->whatsappQuota($user);
            if ($quota['reached']) {
                return response()->json([
                    'message'        => 'Daily WhatsApp limit reached. You can send up to '
                        . self::WHATSAPP_DAILY_LIMIT . ' invitations every 24 hours.',
                    'whatsapp_quota' => $quota,
                ], 429);
            }
        }

        $token = Str::random(10);

        $link = InvitationLink::create([
            'recipient_id'       => $request->recipient_id,
            'document_id'        => $request->document_id,
            'token'              => $token,
            'via'                => $request->via ?? 'WhatsApp',
            'created_by_user_id' => $user?->id,
            'expires_at'         => null,
        ]);

        // Also mark recipient as sent
        Recipient::where('id', '=', $request->recipient_id)->update(['sent' => true]);

        $payload = $link->load(['recipient', 'document'])->toArray();

        // Surface the updated quota so the UI can hide send buttons once the
        // cap is reached, without a second round-trip.
        if ($user) {
            $payload['whatsapp_quota'] = $this->whatsappQuota($user);
        }

        return response()->json($payload, 201);
    }

    /**
     * Return the current user's rolling-24h WhatsApp quota snapshot.
     */
    public function quota(Request $request)
    {
        return response()->json($this->whatsappQuota($request->user()));
    }

    /**
     * Build a snapshot of how many WhatsApp invitations the given user has
     * sent in the last 24 hours and how many remain.
     */
    private function whatsappQuota(User $user): array
    {
        $base = InvitationLink::where('created_by_user_id', $user->id)
            ->where('via', 'WhatsApp')
            ->where('created_at', '>=', now()->subDay());

        $limit     = self::WHATSAPP_DAILY_LIMIT;
        $used      = (clone $base)->count();
        $remaining = max(0, $limit - $used);
        $reached   = $used >= $limit;

        // Once the cap is hit, a slot frees up 24h after the oldest send that
        // is still inside the current window.
        $resetsAt = null;
        if ($reached) {
            $oldest = (clone $base)->orderBy('created_at')->value('created_at');
            $resetsAt = $oldest ? Carbon::parse($oldest)->addDay()->toIso8601String() : null;
        }

        return [
            'limit'     => $limit,
            'used'      => $used,
            'remaining' => $remaining,
            'reached'   => $reached,
            'resets_at' => $resetsAt,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return InvitationLink::with(['recipient', 'document.fields'])
            ->where('token', $id)
            ->firstOrFail();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $link = InvitationLink::findOrFail($id);
        $link->delete();
        return response()->json(null, 204);
    }
}
