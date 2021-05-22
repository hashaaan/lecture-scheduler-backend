<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HallAllocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $h = $this->hall()->first();
        $hall = new HallResource($h);
        return [
            "hall" => $hall,
            "schedule_id"=>$this->schedule_id,
            "note"=>$this->note,
            "lecturer_id"=>$this->lecturer_id,
            "student_count"=>$this->student_count
        ];
    }
}
