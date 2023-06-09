<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cell extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CellCode';

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'SectorCode');
    }

    public function villages()
    {
        return $this->hasMany(Village::class, 'CellCode');
    }
}
