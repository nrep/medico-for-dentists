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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        /* static::created(function ($invoiceDay) {
            foreach ($invoiceDay->invoice->days as $key => $day) {
                $day->number = $key + 1;
                $day->save();
            }
        });

        static::deleted(function ($invoiceDay) {
            foreach ($invoiceDay->invoice->days as $key => $day) {
                $day->number = $key + 1;
                $day->save();
            }
        }); */
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function consultedBy()
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }
}
