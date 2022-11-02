<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'DistrictCode';

    public function province()
    {
        return $this->belongsTo(Province::class, 'ProvinceCode');
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class, 'DistrictCode');
    }
}
