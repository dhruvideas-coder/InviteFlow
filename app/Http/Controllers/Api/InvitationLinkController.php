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
     * Maximum number of WhatsApp invitations a single user may *confirm* as
     * sent within any rolling 24-hour window. Other delivery methods (e.g.
     * plain links) are not counted against this cap.
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
     *
     * WhatsApp links are created in a "pending" state (confirmed_at = null):
     * they do not count toward the daily limit and the recipient is not marked
     * as sent until the sender confirms the message actually went out. Other
     * delivery methods are confirmed immediately.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:recipients,id',
            'document_id' => 'required|exists:documents,id',
            'via' => 'string',
        ]);

        $user = $request->user();
        $via = $request->via ?? 'WhatsApp';
        $isWhatsApp = $via === 'WhatsApp';

        // Guard the per-user daily WhatsApp cap before handing out a new link.
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

        $link = InvitationLink::create([
            'recipient_id'       => $request->recipient_id,
            'document_id'        => $request->document_id,
            'token'              => Str::random(10),
            'via'                => $via,
            // WhatsApp starts pending; everything else is immediately active.
            'confirmed_at'       => $isWhatsApp ? null : now(),
            'created_by_user_id' => $user?->id,
            'expires_at'         => null,
        ]);

        // Non-WhatsApp links mark the recipient as sent right away. WhatsApp
        // waits for an explicit "Sent" confirmation (see confirm()).
        if (!$isWhatsApp) {
            Recipient::where('id', '=', $request->recipient_id)->update(['sent' => true]);
        }

        $payload = $link->load(['recipient', 'document'])->toArray();

        if ($user) {
            $payload['whatsapp_quota'] = $this->whatsappQuota($user);
        }

        return response()->json($payload, 201);
    }

    /**
     * Confirm that a pending WhatsApp invitation was actually sent. Only then
     * does it count toward the daily limit and mark the recipient as sent.
     */
    public function confirm(Request $request, InvitationLink $link)
    {
        $user = $request->user();

        // Already confirmed — nothing to do, just echo current state.
        if ($link->confirmed_at) {
            return response()->json(array_merge(
                $link->load(['recipient', 'document'])->toArray(),
                ['whatsapp_quota' => $user ? $this->whatsappQuota($user) : null],
            ));
        }

        // Re-check the cap at confirm time: a user could have opened several
        // chats before confirming any of them.
        if ($user) {
            $quota = $this->whatsappQuota($user);
            if ($quota['reached']) {
                return response()->json([
                    'message'        => 'Daily WhatsApp limit reached. You can send up to '
                        . self::WHATSAPP_DAILY_LIMIT . ' invitations every 24 hours.',
                    'whatsapp_quota' => $quota,
                ], 429);
            }
        }

        $link->update(['confirmed_at' => now()]);
        Recipient::where('id', '=', $link->recipient_id)->update(['sent' => true]);

        $payload = $link->load(['recipient', 'document'])->toArray();
        if ($user) {
            $payload['whatsapp_quota'] = $this->whatsappQuota($user);
        }

        return response()->json($payload);
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
     * *confirmed as sent* in the last 24 hours and how many remain.
     */
    private function whatsappQuota(User $user): array
    {
        $base = InvitationLink::where('created_by_user_id', $user->id)
            ->where('via', 'WhatsApp')
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '>=', now()->subDay());

        $limit     = self::WHATSAPP_DAILY_LIMIT;
        $used      = (clone $base)->count();
        $remaining = max(0, $limit - $used);
        $reached   = $used >= $limit;

        // Once the cap is hit, a slot frees up 24h after the oldest confirmed
        // send still inside the current window.
        $resetsAt = null;
        if ($reached) {
            $oldest = (clone $base)->orderBy('confirmed_at')->value('confirmed_at');
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
        $link = InvitationLink::with(['recipient', 'document.fields', 'document.user.parent'])
            ->where('token', $id)
            ->firstOrFail();

        // Resolve the host/organizer from the document owner's tenant (admin).
        // Only a whitelisted, public-safe subset is exposed — never the full
        // user record (which holds email/google_id).
        $host = optional($link->document?->user)->tenant();

        // The host name follows the document's language: Gujarati documents show
        // the Gujarati name, everything else the English one — each falling back
        // to the other variant, then to the admin's account name.
        $hostName = null;
        if ($host) {
            $hostName = $link->document?->language === 'gu'
                ? ($host->host_name_gu ?: $host->host_name_en)
                : ($host->host_name_en ?: $host->host_name_gu);
            $hostName = $hostName ?: $host->name;
        }

        $payload = $link->toArray();
        $payload['host'] = $host ? [
            'name'         => $hostName,
            'organization' => $host->organization,
            'image_url'    => $host->host_image_url,
        ] : null;

        return response()->json($payload);
    }

    /**
     * Remove the specified resource from storage. Used by the "Not Sent"
     * action to discard a pending WhatsApp link the user never actually sent.
     */
    public function destroy(string $id)
    {
        $link = InvitationLink::findOrFail($id);
        $link->delete();
        return response()->json(null, 204);
    }
}
