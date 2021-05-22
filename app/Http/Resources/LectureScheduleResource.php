<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LectureScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lecturer = $this->lecturer()->get();
        $module = $this->module()->get();
        $schedule_batches = $this->batches()->get();
        $batches_list = [];
        $hall_all = $this->hall_allocation()->first();
        $halls = new HallAllocationResource($hall_all);

        foreach ($schedule_batches as $schedule_batch){
            $batch = $schedule_batch->batch()->first();
            array_push($batches_list,$batch);
        }
        return [
            'id'=>$this->id,
            'hall_allocation_id'=>$this->hall_allocation_id,
            'module_id'=>$this->module_id,
            'module'=>$module,
            'from'=>$this->from,
            'to'=>$this->to,
            'no_of_students'=>$this->no_of_students,
            'date'=>Carbon::create($this->date)->toDateString(),
            'lecturer_id'=>$this->lecturer_id,
            'status'=>$this->status,
            'lecturer'=>$lecturer,
            'batches'=>$batches_list,
            'hall_data'=>$halls
//            'division'=>$type,
//            'hall'=>$hall,
        ];
    }
}
