<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'SectorCode';

    public function district()
    {
        return $this->belongsTo(District::class, 'DistrictCode');
    }

    public function cells()
    {
        return $this->hasMany(Cell::class, 'SectorCode');
    }
}
