<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignedSubject extends Model
{

    protected $fillable = [
        'subjectId','userId','classId', 'sessionId', 'school_id'
    ];

    public function User() {
        return $this->belongsTo('App\User');
    }
}
