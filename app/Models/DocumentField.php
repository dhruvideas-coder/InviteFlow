<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentField extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'field_type', 'label', 'color',
        'x_percent', 'y_percent', 'width_percent', 'height_percent',
        'page_number', 'required', 'sort_order',
    ];

    protected $casts = [
        'required'       => 'boolean',
        'x_percent'      => 'float',
        'y_percent'      => 'float',
        'width_percent'  => 'float',
        'height_percent' => 'float',
        'page_number'    => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
