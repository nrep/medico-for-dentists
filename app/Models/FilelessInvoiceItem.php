<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilelessInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileless_invoice_id',
        'charge_id',
        'quantity',
        'amount',
        'done_by'
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }
}
