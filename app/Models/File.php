<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'names',
        'sex',
        'year_of_birth',
        'phone_number',
        'registration_date',
        'registration_year',
        'location',
        'legacy_db_member_id',
        'created_at',
        'updated_at',
        'full_number'
    ];

    protected $casts = [
        'location' => 'array',
    ];

    public function linkedInsurances()
    {
        return $this->hasMany(FileInsurance::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(FileEmergencyContact::class);
    }

    // public function het
}
