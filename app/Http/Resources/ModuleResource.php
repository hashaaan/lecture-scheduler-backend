<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $batch_modules = $this->batches()->get();
        $batches = [];
        $lecturers = [];
        foreach ($batch_modules as $batch_module)
        {
            array_push($batches,$batch_module->batch()->first());
            array_push($lecturers, $batch_module->lecturer()->first());
        }
        return [
            'title'=>$this->title,
            'code'=>$this->code,
            'description'=>$this->description,
            'theory_hours'=>$this->theory_hours,
            'practical_hours'=>$this->practical_hours,
            'status'=>$this->status,
            'batches'=>$batches,
            'lecturers'=>$lecturers
        ];
    }
}
