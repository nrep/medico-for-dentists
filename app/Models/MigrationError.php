<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MigrationError extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_table_type',
        'from_table_id',
        'to_table_type',
        'to_table_id',
        'model_title',
        'data',
        'error_message',
        'error_title',
        'resolved',
        'comment'
    ];

    protected $casts = [
        'data' => 'array',
        'error_message' => 'array'
    ];
}
