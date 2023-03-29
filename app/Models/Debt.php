<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'debtable_type',
        'debtable_id',
        'amount',
        'payment_date',
        'payment_status',
        'payer_name',
        'payer_phone_number',
        'comment'
    ];

    public function debtable()
    {
        return $this->morphTo();
    }
}
