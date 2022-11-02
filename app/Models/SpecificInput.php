<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecificInput extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "type",
        "default_value",
        "options"
    ];
    
    protected $casts = [
        'options' => 'array',
    ];
}
