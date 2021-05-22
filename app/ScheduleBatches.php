<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleBatches extends Model
{
    protected $table = 'schedule_batches';

    protected $fillable = [
        'lecture_schedule_id',
        'batch_id',
    ];
    //
    public function lecture_schedule(){
        return $this->belongsTo(LectureSchedule::class,'id','lecture_schedule_id');
    }
    public function  batch(){
        return $this->belongsTo(Batch::class);
    }
}
