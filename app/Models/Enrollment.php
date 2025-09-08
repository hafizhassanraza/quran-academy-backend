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
        'status',
        'grade',
        'semester',
        'year',
        'other',

    ];
}
