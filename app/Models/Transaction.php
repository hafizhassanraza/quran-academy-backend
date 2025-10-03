<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    
    protected $fillable = [
        'teacher_id',
        'amount',
        'type',
        'status',
        'reference',
        'other',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
