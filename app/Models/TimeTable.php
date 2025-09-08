<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable = [
        'day',
        'start_time',
        'end_time',
        'student_id',
        'course_id',
        'chapter_id',
        'teacher_id',
        'status',
        'slot_number',
        'created_by',
        'updated_by',
    ];
}
