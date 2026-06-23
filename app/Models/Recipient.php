<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $fillable = [
        'name_en',
        'name_gu',
        'mobile',
        'village_en',
        'village_gu',
        'sent',
        'created_by_user_id',
    ];

    protected $casts = [
        'sent' => 'boolean',
    ];

    public function invitationLinks()
    {
        return $this->hasMany(InvitationLink::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
