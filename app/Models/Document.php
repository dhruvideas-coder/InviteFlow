<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'status',
        'total_signers',
        'completed_signers',
        'language',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $appends = ['file_url'];

    public function getFileUrlAttribute()
    {
        return $this->file_path ? '/file/' . $this->file_path : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(DocumentField::class)->orderBy('sort_order');
    }
}
