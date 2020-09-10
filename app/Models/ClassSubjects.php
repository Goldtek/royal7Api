<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubjects extends Model
{
    protected $fillable = [
        'class_id','school_id','subject_id'
    ];
}
