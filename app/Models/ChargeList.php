<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ChargeList extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        "title",
        'source_file',
        'valid_since',
        'valid_until',
        'enabled'
    ];

    protected $casts = [
        "source_file" => "json"
    ];

    public function linkedInsurances()
    {
        return $this->hasMany(ChargeListInsurance::class);
    }

    public function linkedChargeTypes()
    {
        return $this->hasMany(ChargeListChargeType::class);
    }
}
