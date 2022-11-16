<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilelessInvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileless_invoice_id',
        'payment_mean_id',
        'amount',
        'done_by'
    ];

    public function paymentMean()
    {
        return $this->belongsTo(PaymentMean::class);
    }
}
