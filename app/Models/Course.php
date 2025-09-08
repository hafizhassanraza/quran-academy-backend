<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    
    protected $fillable = [
        'course_code',
        'course_name',
        'description',
        'credits',
        'department',
        'schedule',
        'enrollment_count',
        'status',
        'other'

    ];
}
