<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'teacher_id',
        'enrollment_date',
        'starting_date',
        'ending_date',
        'slots',
        'status',
        'grade',
        'year',
        'other',
    ];

    protected $casts = [
        'slots' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

   

}
