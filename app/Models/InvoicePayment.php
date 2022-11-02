<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_mean_id',
        'invoice_id',
        'amount',
        'done_by'
    ];

    public function paymentMean()
    {
        return $this->belongsTo(PaymentMean::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
