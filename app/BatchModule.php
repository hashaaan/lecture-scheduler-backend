<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchModule extends Model
{
    protected $table = 'batch_modules';

    protected $fillable = [
        'batch_id',
        'module_id',
        'lecturer',
    ];

    public function module(){
        return $this->belongsTo(Module::class);
    }
    public function batch(){
        return $this->belongsTo(Batch::class);
    }

    public function lecturer(){
        return $this->belongsTo(Lecturer::class,'lecturer','id');
    }
}
