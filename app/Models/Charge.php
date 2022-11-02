<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Charge extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "charge_list_charge_type_id",
        "name",
        "price",
        'valid_since',
        'valid_until',
        'enabled'
    ];

    public function conditions()
    {
        return $this->hasMany(ChargeCondition::class);
    }

    public function chargeListChargeType()
    {
        return $this->belongsTo(ChargeListChargeType::class);
    }
}
