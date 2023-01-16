<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'file_insurance_id',
        'discount_id',
        'date',
        'specific_data',
        'done_by'
    ];

    protected $casts = [
        'specific_data' => 'array',
    ];

    public function fileInsurance()
    {
        return $this->belongsTo(FileInsurance::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
