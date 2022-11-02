<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmployeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_id",
        "employee_category_id"
    ];

    protected $table = "employee_employee_category";

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(EmployeeCategory::class, "employee_category_id");
    }
}
