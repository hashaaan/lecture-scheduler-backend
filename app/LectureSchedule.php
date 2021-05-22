<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureSchedule extends Model
{
    protected $table = 'lecture_schedules';

    protected $fillable = [
                            'hall_allocation_id',
                            'module_id',
                            'from',
                            'to',
                            'no_of_students',
                            'date',
                            'lecturer_id',
                            'status',
                          ];
    public function batches(){
        return $this->hasMany(ScheduleBatches::class,'lecture_schedule_id','id');
    }

    public function module(){
        return $this->belongsTo(Module::class);
    }

    public function lecturer(){
        return $this->hasOne(Lecturer::class,'id','lecturer_id');
    }
    public function hall_allocation(){
        return $this->hasOne(HallAllocation::class,"schedule_id","id");
    }
}
