<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    protected $fillable = [
        'start_time','end_time','date','userId','schoolId','sessionId','subjectId', 'classId'
    ];
}
