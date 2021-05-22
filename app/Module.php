<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'title',
        'description',
        'theory_hours',
        'practical_hours',
        'status',
        'code',
    ];


    public function batches()
    {
        return $this->hasMany(BatchModule::class);
    }

    public function lectureschedules()
    {
        return $this->hasMany(LectureSchedule::class);
    }
}
