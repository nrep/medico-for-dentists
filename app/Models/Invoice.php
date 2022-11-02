<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "session_id",
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function days()
    {
        return $this->hasMany(InvoiceDay::class);
    }

    public function charges()
    {
        return $this->hasManyThrough(InvoiceItem::class, InvoiceDay::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
