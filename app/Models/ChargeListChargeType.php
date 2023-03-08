<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ChargeListChargeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_list_id',
        'charge_type_id',
        'valid_since',
        'valid_until',
        'enabled'
    ];

    public function chargeType()
    {
        return $this->belongsTo(ChargeType::class);
    }

    public function getChargeTypeNameAttribute()
    {
        return $this->chargeType->name;
    }

    public function chargeList()
    {
        return $this->belongsTo(ChargeList::class);
    }

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    /**
     * Scope a query to only include enabled entries.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('enabled', 1);
    }
}
