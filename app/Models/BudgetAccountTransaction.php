<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetAccountTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'budget_account_id',
        'budget_source_id',
        'nature',
        'amount',
        'date',
        'description',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function ($transaction) {
            $balance = 0;
            foreach ($transaction->account->transactions as $key => $accountTransaction) {
                if ($accountTransaction->nature == 'credit') {
                    $balance += $accountTransaction->amount;
                } else {
                    $balance -= $accountTransaction->amount;
                }

                $accountTransaction->balance = $balance;

                $accountTransaction->save();
            }
        });

        static::deleted(function ($transaction) {
            $balance = 0;
            foreach ($transaction->account->transactions as $key => $accountTransaction) {
                if ($accountTransaction->nature == 'credit') {
                    $balance += $accountTransaction->amount;
                } else {
                    $balance -= $accountTransaction->amount;
                }

                $accountTransaction->balance = $balance;

                $accountTransaction->save();
            }
        });

        static::updated(function ($transaction) {
            $balance = 0;
            foreach ($transaction->account->transactions as $key => $accountTransaction) {
                if ($accountTransaction->nature == 'credit') {
                    $balance += $accountTransaction->amount;
                } else {
                    $balance -= $accountTransaction->amount;
                }

                $accountTransaction->balance = $balance;

                $accountTransaction->save();
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(BudgetAccount::class, 'budget_account_id');
    }

    public function source()
    {
        return $this->belongsTo(BudgetSource::class, 'budget_source_id');
    }
}
