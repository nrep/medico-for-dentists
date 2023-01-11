<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'initial_amount',
        'year',
    ];
}
