<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HallAllocation extends Model
{
    protected $table = "hall_allocations";

    protected $fillable = [
        "hall_id",
        "schedule_id",
        "note",
        "lecture_id",
        "student_count"
    ];

    public function lecture_schedule(){
        return $this->belongsTo(LectureSchedule::class,'id','schedule_id');
    }

    public function hall(){
        return $this->belongsTo(Hall::class,'hall_id','id');
    }


}
