<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
     'student_id', 'test', 'exam', 'subject_id', 'average', 'section_id', 
    ];

    public function Student() {
        return $this->belongsTo('App\Student');
    }
}
