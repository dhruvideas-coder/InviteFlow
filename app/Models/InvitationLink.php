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
        'confirmed_at',
        'created_by_user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
