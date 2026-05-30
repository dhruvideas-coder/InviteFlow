<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvitationLink;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationLinkController extends Controller
{
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

        $token = Str::random(10);
        
        $link = InvitationLink::create([
            'recipient_id'       => $request->recipient_id,
            'document_id'        => $request->document_id,
            'token'              => $token,
            'via'                => $request->via ?? 'WhatsApp',
            'created_by_user_id' => $request->user()?->id,
            'expires_at'         => null,
        ]);

        // Also mark recipient as sent
        Recipient::where('id', '=', $request->recipient_id)->update(['sent' => true]);

        return response()->json($link->load(['recipient', 'document']), 201);
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
