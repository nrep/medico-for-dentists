<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        'enabled'
    ];

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function linkedFiles()
    {
        return $this->hasMany(FileInsurance::class);
    }

    public function linkedChargeLists()
    {
        return $this->hasMany(ChargeListInsurance::class);
    }

    public function sessions() {
        return $this->hasManyThrough(Session::class, FileInsurance::class);
    }
}
