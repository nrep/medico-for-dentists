<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'budget_account_id',
        'bill_no',
        'expenseable_type',
        'expenseable_id',
        'payment_mean_id',
        'date',
        'comment'
    ];

    public function account()
    {
        return $this->belongsTo(BudgetAccount::class, 'budget_account_id');
    }

    public function paymentMean()
    {
        return $this->belongsTo(PaymentMean::class, 'payment_mean_id');
    }

    public function expenseable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }
}
