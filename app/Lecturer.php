<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    protected $table = 'lecturers';

    protected $fillable = [
        'full_name',
        'nibm_id',
        'lecturer_type',
        'email',
        'contact_no',
        'status'
    ];

    public function batchmodules(){
        return $this->hasMany(BatchModule::class,'id','lecturer');
    }

    public function lectureschedule(){
        return $this->hasMany(LectureSchedule::class);
    }
}
