<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{


    
    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'enrollment_count',
        'status',
        'other'

    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
    public function teachers()
    {
        return $this->hasManyThrough(Teacher::class, Enrollment::class, 'course_id', 'id', 'id', 'teacher_id');
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Enrollment::class, 'course_id', 'id', 'id', 'student_id');
    }



}
