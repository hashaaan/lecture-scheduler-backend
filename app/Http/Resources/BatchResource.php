<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $b_type = $this->batchtype()->get();
        return [
            'id'=> $this->id,
            'batch_number' => $this->batch_number,
            'description'=> $this->description,
            'batch_type'=>$b_type,
            'no_of_students'=>$this->no_of_students,
            'name'=>$this->name,
            'start_date'=>$this->start_date,
            'end_date'=>$this->end_date,
            'lecturer_id'=>$this->lecturer_id,
            'status'=>$this->status,
        ];
    }
}
