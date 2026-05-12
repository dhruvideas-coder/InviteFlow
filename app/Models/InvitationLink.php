<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationLink extends Model
{
    protected $fillable = [
        'recipient_id',
        'document_id',
        'token',
        'via',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
