<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnclassifiedColumn extends Model
{
    use HasFactory;

    protected $table = 'unclassified_columns';

    protected $fillable = [
        'file_name',
        'missing_required_fields',
        'unclassified_headers',
        'status',
    ];

    protected $casts = [
        'missing_required_fields' => 'array',
        'unclassified_headers'   => 'array',
    ];
}