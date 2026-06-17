<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'language',
        'body',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
