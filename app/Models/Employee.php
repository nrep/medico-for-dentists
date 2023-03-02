<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        "names",
        "sex",
        "phone_number",
        "employee_category_id",
        "degree",
        "started_at",
        "specific_data"
    ];

    protected $casts = [
        'specific_data' => 'json',
    ];

    public function categories()
    {
        return $this->hasMany(EmployeeEmployeeCategory::class);
    }

    public function days()
    {
        return $this->hasMany(InvoiceDay::class, 'doctor_id');
    }
}
