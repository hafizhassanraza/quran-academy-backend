<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    
    protected $fillable = [

        'teacher_id',
        'student_id',
        'course_id',
        'chapter_id',
        'slot_number',
        'slot_date',
        'reschedule_date',
        'start_time',
        'end_time',
        'status',
        'other',


    ];
}
