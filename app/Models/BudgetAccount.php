<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function transactions()
    {
        return $this->hasMany(BudgetAccountTransaction::class);
    }
}
