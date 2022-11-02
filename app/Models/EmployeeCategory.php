<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "type"
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function specificInputs()
    {
        return $this->morphMany(SpecificInput::class, 'modelable');
    }
}
