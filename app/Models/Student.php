<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    

    protected $fillable = [
        'registration_no',
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
        'enrollment_date',
        //'username',
        'password',
        'last_login',
        'national_id',
        'time_zone',
        'other',
        'status',
    ];


    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function Slots()
    {
        return $this->hasManyThrough(
            Slot::class,
            Enrollment::class,
            'student_id', // Foreign key on enrollments table...
            'enrollment_id', // Foreign key on slots table...
            'id', // Local key on teachers table...
            'id'  // Local key on enrollments table...
        );
    }
    

    public function courses()
    {
        return $this->hasManyThrough(Course::class, Enrollment::class, 'student_id', 'id', 'id', 'course_id');
    }
    public function teachers()
    {
        return $this->hasManyThrough(Teacher::class, Enrollment::class, 'student_id', 'id', 'id', 'teacher_id');
    }

    


}
