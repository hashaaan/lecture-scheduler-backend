<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $h_type = $this->halltype()->first();

        return [
            'id'=>$this->id,
            'hall_no'=>$this->hall_no,
            'hall_name'=>$this->hall_name,
            'category'=>$this->category,
            'hall_type'=>$h_type,
            'capacity'=>$this->capacity,
        ];
    }
}
