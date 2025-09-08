<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [

        'course_id',
        'chapter_title',
        'chapter_number',
        'content',

    ];
}
