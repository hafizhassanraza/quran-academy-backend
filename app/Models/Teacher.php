<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'employee_id',
        'photo',
        'full_name',
        'father_name',
        'gender',
        'age',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'country',
        'hire_date',
        'username',
        'password',
        'last_login',
        'national_id',
        'time_zone',
        'other',
        'status',
    ];


    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function studentEnrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
    

}
