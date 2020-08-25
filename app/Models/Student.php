<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'class_id', 'session_id', 'user_id', 'school_id'
    ];

    public function User() {
        return $this->belongsTo('App\User');
    }

    public function Scores(){
        return $this->hasMany('App\Models\Score');
    }
}
