<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilelessInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'names',
        'date',
        'done_by'
    ];

    public function items()
    {
        return $this->hasMany(FilelessInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(FilelessInvoicePayment::class);
    }
}
