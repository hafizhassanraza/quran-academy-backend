<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    
    protected $fillable = [

        'enrollment_id',
        'chapter_id',
        'slot_code',
        'slot_date',
        'reschedule_date',
        'start_time',
        'end_time',
        'status',
        'other',

    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Scope to filter rescheduled slots
    // Usage: Slot::rescheduled()->get();
    public function scopeRescheduled($query)
    {
        return $query->where('status', 'rescheduled');
    }

    public function scopeSlotOfTeacher($query, $teacherId)
    {
        return $query->whereHas('enrollment', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        });
    }





}
