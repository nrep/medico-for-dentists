<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'budget_line_id',
        'amount',
        'reason',
        'ebm_billed',
        'ebm_bill_number',
        'comment',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function line()
    {
        return $this->belongsTo(BudgetLine::class, 'budget_line_id');
    }
}
