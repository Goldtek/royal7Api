<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable = [
        'start_time','end_time','date','teacher','schoolId','sessionId','subjectId', 'supervisor', 'classId'
    ];
}
