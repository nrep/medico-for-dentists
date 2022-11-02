<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeListInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_list_id',
        'insurance_id',
        'valid_since',
        'valid_until',
        'enabled'
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function getInsuranceNameAttribute()
    {
        return $this->insurance->name;
    }

    public function chargeList()
    {
        return $this->belongsTo(ChargeList::class);
    }
}
