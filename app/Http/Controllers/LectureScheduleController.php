<?php

namespace App\Http\Controllers;

use App\Batch;
use App\BatchType;
use App\HallAllocation;
use App\Hall;
use App\Http\Resources\LectureScheduleCollection;
use App\Http\Resources\LectureScheduleResource;
use App\LectureSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Array_;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use function PHPUnit\Framework\isNull;

class LectureScheduleController extends Controller
{
    //
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
                'data' => ['required','array'],
                'data.*.key'=> ['required','integer'],
            ]
        );


        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors()->all(),
                    'status_code' => 400
                ], 400);
        }
        $datas = $request->data;
        $lectures = [];
        $valid = true;
        $validator_msgs = [];
        foreach ($datas as $data)
        {
            $err_msgs = [
                'module_id.required'=> 'module_id is required for row '.$data["key"],
                'module_id.exists'=> 'Module is invalid for row '.$data["key"],
                'starttime.required'=> 'starttime is required for row '.$data["key"],
                'starttime.date_format'=> 'starttime is invalid for row '.$data["key"],
                'endtime.required'=> 'endtime is required for row '.$data["key"],
                'endtime.date_format'=> 'endtime is invalid for row '.$data["key"],
                'date.required'=> 'date is required for row '.$data["key"],
                'date.date_format'=> 'date is invalid for row '.$data["key"],
                'status.required'=> 'status is required for row '.$data["key"],
                'status.integer'=> 'status should be a integer for row '.$data["key"],
                'batches.required'=>'batches are required for row '.$data["key"],
                'batches.array'=>'batches should be a array for row '.$data["key"],
//                'data.*.batches.*.batch_id'=>['required','integer','exists:batches,id'],
//                'batches.*'=>['required','integer','exists:batches,id'],

                'hall_id.required'=> 'hall is required for row '.$data["key"],
                'hall_id.exists'=> 'hall is invalid for row '.$data["key"],
                'lecturer_id.required'=> 'lecturer is required for row '.$data["key"],
                'lecturer_id.exists'=> 'lecturer is invalid for row '.$data["key"],
                'lecturer_id.unique'=>'lecturer is already booked for row '.$data["key"],
            ];

            $validator = Validator::make($data,[
                'module_id'=> ['required','exists:modules,id'],
                'starttime'=> ['required','date_format:Hi'],
                'endtime'=> ['required','date_format:Hi'],
                'date'=>['required','date_format:Y/m/d'],
                'status'=> ['required','Integer'],
                'batches'=>['required','array'],
//                'data.*.batches.*.batch_id'=>['required','integer','exists:batches,id'],
                'hall_id'=>['required','exists:halls,hall_no'],
                'lecturer_id' => [ 'required','exists:lecturers,id',
                                        Rule::unique('lecture_schedules')->where(function ($query) use ($data) {
                                        return $query
                                            ->wherelecturer_id($data['lecturer_id'])
                                            ->where('lecture_schedules.from','=',Carbon::parse($data["date"]. ' ' .$data["starttime"]))
                                            ->orwherebetween('lecture_schedules.from',[Carbon::parse($data["date"]. ' ' .$data["starttime"]),Carbon::parse($data["date"]. ' ' .$data["endtime"])])
                                            ->orwherebetween('lecture_schedules.to',[Carbon::parse($data["date"]. ' ' .$data["starttime"]),Carbon::parse($data["date"]. ' ' .$data["endtime"])]);
                                        }),
                                 ],

                ],
                $err_msgs
            );
            if($validator->fails()){
                return \Response::json(
                    [
                        'error'=>true,
                        'response' => $validator->errors()->all(),
                        'saved'=> $lectures,
                        'status_code' => 400
                    ], 400);
            }

            $check = \DB::table('lecture_schedules')
                ->join('hall_allocations',
                    function ($join) {
                        $join->on('hall_allocations.schedule_id', '=', 'lecture_schedules.id');
                    })
                ->where('hall_allocations.hall_id','=',$data["hall_id"])
                ->where('lecture_schedules.from','=',Carbon::parse($data["date"]. ' ' .$data["starttime"]))
                ->orwherebetween('lecture_schedules.from',[Carbon::parse($data["date"]. ' ' .$data["starttime"]),Carbon::parse($data["date"]. ' ' .$data["endtime"])])
                ->orwherebetween('lecture_schedules.to',[Carbon::parse($data["date"]. ' ' .$data["starttime"]),Carbon::parse($data["date"]. ' ' .$data["endtime"])])
                ->get();
            if($check->isEmpty()){

            }
            else{
                return \Response::json(
                    [
                        'error'=> true,
                        'response' => "conflicting schedule exists for hall ".$data["hall_id"]." from ".$data["starttime"]." to ".$data["endtime"]." on ".$data["date"],
                        'check'=>$check,
                        'status_code' => 400
                    ], 400);
            }

            $batches = $data["batches"];
            $no_of_students = 0;

            foreach ($batches as $batch) {
                $validator = Validator::make(['batch_id' => $batch], [
                        'batch_id' => ['required', 'integer', 'exists:batches,id'],
                    ]
                    ,
                    [
                        'batch_id.required' => 'batch_id is required for row ' . $data["key"],
                        'batch_id.integer' => 'batch should be a integer for row ' . $data["key"],
                        'batch_id.exists' => 'Batch ' . $batch . ' is invalid for row ' . $data["key"],
                    ]
                );
                if ($validator->fails()) {
                    $valid = false;
                    array_push($validator_msgs, $validator->errors()->all());
                } else {
                    foreach ($datas as $data2) {
                        if ($data["key"] != $data2["key"]) {
                            foreach ($batches as $batch2) {
                                if ($batch == $batch2) {
                                    if ($data2["date"] == $data2["date"]) {
                                        if (($data["starttime"] <= $data2["starttime"]) && ($data2["starttime"] < $data["endtime"])) {
                                            return \Response::json(
                                                [
                                                    'error' => true,
                                                    'response' => "conflicting schedule exists for batch " . $batch . " from " . $data2["starttime"] . " to " . $data["endtime"] . " on " . $data["date"],
                                                    'lectures' => $lectures,
                                                    'status_code' => 400
                                                ], 400);
                                        }
                                        if (($data["starttime"] < $data2["endtime"]) && ($data2["endtime"] < $data["endtime"])) {
                                            return \Response::json(
                                                [
                                                    'error' => true,
                                                    'response' => "conflicting schedule exists for batch " . $batch . " from " . $data2["starttime"] . " to " . $data["endtime"] . " on " . $data["date"],
                                                    'lectures' => $lectures,
                                                    'status_code' => 400
                                                ], 400);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //checking if there is coflicting schedules for batches saved
                    $check = \DB::table('lecture_schedules')
                        ->join('schedule_batches',
                            function ($join) {
                                $join->on('schedule_batches.lecture_schedule_id', '=', 'lecture_schedules.id');
                            })
                        ->where('schedule_batches.batch_id', '=', $batch)
                        ->where('lecture_schedules.from', '=', Carbon::parse($data["date"] . ' ' . $data["starttime"]))
                        ->orwherebetween('lecture_schedules.from', [Carbon::parse($data["date"] . ' ' . $data["starttime"]), Carbon::parse($data["date"] . ' ' . $data["endtime"])])
                        ->orwherebetween('lecture_schedules.to', [Carbon::parse($data["date"] . ' ' . $data["starttime"]), Carbon::parse($data["date"] . ' ' . $data["endtime"])])
                        ->get();
                    if ($check->isEmpty()) {

                    } else {
                        return \Response::json(
                            [
                                'error' => true,
                                'response' => "conflicting schedule exists for batch " . $batch . " from " . $data["starttime"] . " to " . $data["endtime"] . " on " . $data["date"],
                                'check' => $check,
                                'status_code' => 400
                            ], 400);
                    }
                    $found = Batch::findorfail($batch);
                    $no_of_students = $no_of_students + $found->no_of_students;
                }
            }

            if($valid){
                $schedule = [
                    'hall_allocation_id' => 0,
                    'module_id' => $data["module_id"],
                    'no_of_students' => $no_of_students,
                    'from' => Carbon::parse($data["date"] . ' ' . $data["starttime"]),
                    'to' => Carbon::parse($data["date"] . ' ' . $data["endtime"]),
                    'date' => $data["date"],
                    'lecturer_id' => $data["lecturer_id"],
                    'status' => $data["status"],
                ];
                $lecture = LectureSchedule::create($schedule);
                $hallallocation = $lecture->hall_allocation()->create(['hall_id' => $data["hall_id"], 'lecture_id' => $data["lecturer_id"], 'student_count' => $no_of_students]);
                $assignedbatches = [];
                foreach ($batches as $batch) {
                    $newasigns = $lecture->batches()->create(['batch_id' => $batch]);
                    $new = Batch::findorfail($newasigns["batch_id"]);
                    array_push($assignedbatches, $new);

                }

                $lecture["batches"] = $assignedbatches;
                $lecture["hall"] = Hall::findorfail(['hall_no' => $hallallocation["hall_id"]]);
                array_push($lectures, $lecture);
            }
        }
        if(!$valid){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator_msgs,
                    'status_code' => 400
                ], 400);
        }
        else {
            return \Response::json(
                [
                    'error' => false,
                    'response' => $lectures,
                    'status_code' => 200
                ], 200);
        }
    }

    public function rule(){
        Validator::extend('batch_conflicts',function ($attribute,$value){

        });
    }

    public function delete($id){
        $validator = Validator::make(['id'=>$id],["id"=>['integer','exists:lecture_schedules']]);
        if($validator->fails()) {
            return \Response::json(
                [
                    'error' => true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $lecture_schedule = LectureSchedule::findOrFail(['id'=>$id])->first();
            $lecture_schedule->batches();
            $lecture_schedule->hall_allocation()->delete();
            $deleted = $lecture_schedule->delete();
            return \Response::json(
                [
                    'error'=> false,
                    'deleted_items' => $deleted,
                    'status_code' => 200
                ], 200);
        }
    }


    public function show($id)
    {
        $validator = Validator::make(['id'=>$id],["id"=>['integer','exists:lecture_schedules']]);
        if($validator->fails()) {
            return \Response::json(
                [
                    'error' => true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $lecture_schedule = LectureSchedule::findOrFail(['id' => $id])->first();
            $schedule = new LectureScheduleResource($lecture_schedule);
            return \Response::json(
                [
                    'error' => false,
                    'schedule' => $schedule,
                    'status_code' => 200
                ], 200);
        }
    }

    public function filter(Request $request){
        if($request->filled('page_size')){
            $page_size = $request->input('page_size');
            if($request->filled(['batch_type','batch'])){


            }
            elseif($request->filled('batch_type')){

                $schedules = LectureSchedule::leftjoin('schedule_batches','lecture_schedules.id','=','schedule_batches.lecture_schedule_id')
                    ->join('batches','schedule_batches.batch_id','=','batches.id')
                    ->join('batch_types','batches.batch_type','=','batch_types.id')
                    ->where('batch_types.id','=',$request->input('batch_type'))
                    ->select('lecture_schedules.*')
                    ->groupBy('lecture_schedules.id')
                    ->orderBy('lecture_schedules.created_at','desc')
                    ->paginate($page_size);

                $lschedules = new LectureScheduleCollection($schedules);

                return \Response::json(
                    [
                        'error' => false,
                        'schedule' => $lschedules,
                        'status_code' => 200
                    ], 200);

            }
            if($request->filled('batch')){

                $schedules = LectureSchedule::leftjoin('schedule_batches','lecture_schedules.id','=','schedule_batches.lecture_schedule_id')
                    ->leftjoin('batches','schedule_batches.batch_id','=','batches.id')
                    ->where('batches.id','=',$request->input('batch'))
                    ->select('lecture_schedules.*')
                    ->groupBy('lecture_schedules.id')
                    ->orderBy('lecture_schedules.created_at','desc')
                    ->paginate($page_size);
                $lschedules = new LectureScheduleCollection($schedules);
                return \Response::json(
                    [
                        'error' => false,
                        'schedule' => $lschedules,
                        'status_code' => 200
                    ], 200);

            }else{

                $schedules = LectureSchedule::orderBy('created_at','desc')->paginate($page_size);
                $lschedules = new LectureScheduleCollection($schedules);
                return \Response::json(
                    [
                        'error' => false,
                        'schedule' => $lschedules,
                        'status_code' => 200
                    ], 200);

            }
        }else{
            return \Response::json(
                [
                    'error' => true,
                    'response' => 'page_size if required',
                    'status_code' => 400
                ], 400);
        }
    }
}
