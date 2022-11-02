<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'insurance_id',
        'specific_data'
    ];

    protected $casts = [
        'specific_data' => 'array',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function getInsuranceNameAttribute()
    {
        return $this->insurance->name;
    }
}
