<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDay extends Model
{
    use HasFactory;

    protected $fillable = [
        "date",
        "doctor_id"
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
