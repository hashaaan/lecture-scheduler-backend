<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{
    protected $table = 'halls';

    protected $fillable = [
        'hall_no',
        'hall_name',
        'category',
        'hall_type',
        'capacity',
    ];

    public function hall_allocation(){
        return $this->hasMany(HallAllocation::class,'hall_id','id');
    }

    public function halltype(){
        return $this->belongsTo(BatchType::class,'hall_type','id');
    }

    public function hall_category()
    {
        return $this->belongsTo(HallCategory::class,'category','id');
    }
}
