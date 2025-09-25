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
        //'username',
        'password',
        'last_login',
        'national_id',
        'time_zone',
        'other',
        'status',
        'balance',
    ];


 

    public function courses()
    {
        return $this->hasManyThrough(
            Course::class,
            Enrollment::class,
            'teacher_id',   // Foreign key on enrollments table...
            'id',           // Foreign key on courses table...
            'id',           // Local key on teachers table...
            'course_id'     // Local key on enrollments table...
        );
    }
    public function studentEnrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function completeSlots()
    {
        return $this->hasManyThrough(
            Slot::class,
            Enrollment::class,
            'teacher_id', // Foreign key on enrollments table...
            'enrollment_id', // Foreign key on slots table...
            'id', // Local key on teachers table...
            'id'  // Local key on enrollments table...
        )->where('slots.status', 'completed');
    }

    public function registeredSlots()
    {
        return $this->hasManyThrough(
            Slot::class,
            Enrollment::class,
            'teacher_id', // Foreign key on enrollments table...
            'enrollment_id', // Foreign key on slots table...
            'id', // Local key on teachers table...
            'id'  // Local key on enrollments table...
        );
    }
    


    

}
