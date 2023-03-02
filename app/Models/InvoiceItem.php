<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "invoice_id",
        "invoice_day_id",
        "charge_id",
        "quantity",
        "sold_at",
        "total_price",
        'done_by'
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function getUnitPriceAttribute()
    {
        return $this->sold_at;
    }

    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->sold_at;
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function day()
    {
        return $this->belongsTo(InvoiceDay::class, 'invoice_day_id');
    }
}
