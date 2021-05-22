<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchType extends Model
{
    //
    protected $table = 'batch_types';

    public function batches()
    {
        return $this->hasMany(\App\Batch::class,'id','batch_type');
    }

    public function halls(){
        return $this->hasMany(Hall::class,'hall_type','id');
    }
}
