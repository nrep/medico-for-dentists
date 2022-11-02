<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        "charge_id",
        "insurances",
        "context",
        "type",
        "condition",
        "enabled"
    ];

    protected $casts = [
        "insurances" => "array",
        "condition" => "json"
    ];
}
