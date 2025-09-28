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
        'active_time',
        'start_time',
        'end_time',
        'type',
        'status',
        'reschedule_reason',// pending-migrations
        'reschedule', //true/false // pending-migrations
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


    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('slot_date', $date);
    }

    public function scopeSlotCode($query, $slotCode)
    {
        return $query->where('slot_code', $slotCode);
    }
    

}
