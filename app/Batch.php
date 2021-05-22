<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'batches';
    //
    protected $fillable = [
        'batch_number',
        'description',
        'batch_type',
        'no_of_students',
        'name',
        'start_date',
        'end_date',
        'lecturer_id',
        'status',
    ];

    public function batchmodule(){
        return $this->hasMany(BatchModule::class);
    }

    public function schedulebatches()
    {
        return $this->hasMany(ScheduleBatches::class);
    }

    public function batchtype()
    {
        return $this->belongsTo(\App\BatchType::class,'batch_type','id');
    }
}
