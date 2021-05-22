<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\BatchType;
use Illuminate\Validation\Rule;
use App\Hall;

class HallController extends Controller
{
    public function getHallsByType($type_id){
        $validator = Validator::make(['type_id'=>$type_id],['type_id'=>['required','exists:batch_types,id']]);

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $batch_type = BatchType::findorfail(['id'=>$type_id])->first();
            $halls= $batch_type->halls()->get();
            return \Response::json(
                [
                    'error'=> false,
                    'halls' => $halls,
                    'type'=> $batch_type,
                    'status_code' => 200
                ], 200);
        }
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(),[
                'hall_no'=>['required','string','max:100','unique:halls,hall_no'],
                'hall_name'=>['required','string','max:200'],
                'category'=>['required','Integer','exists:hall_categories,id'],
                'hall_type'=>['required','Integer','exists:batch_types,id'],
                'capacity'=>['required','Integer'],
            ]
        );

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $data = [
                'hall_no'=>$request->hall_no,
                'hall_name'=>$request->hall_name,
                'category'=>$request->category,
                'hall_type'=>$request->hall_type,
                'capacity'=>$request->capacity,
            ];
            $hall= Hall::create($data);
            return \Response::json(
                [
                    'error'=> false,
                    'hall' => $hall,
                    'status_code' => 200
                ], 200);
        }
    }

    public function getTodaysHallAllocations(Request $request){
        $validator = Validator::make(['page_size'=>$request->input('page_size')], ['page_size'=>['required','integer']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $date = Carbon::now()->toDateString();
            $halls = Hall::leftjoin('hall_allocations','halls.id','=','hall_allocations.hall_id')
                ->leftjoin('lecture_schedules','hall_allocations.schedule_id','=','lecture_schedules.id')
                ->leftjoin('lecturers','lecture_schedules.lecturer_id','=','lecturers.id')
                ->leftjoin('batch_types','halls.hall_type','=','batch_types.id')
                ->leftjoin('modules','lecture_schedules.module_id','=','modules.id')
                ->leftjoin('hall_categories','halls.category','=','hall_categories.id')
                ->where('lecture_schedules.date','=',$date)
                ->select(
                    'halls.id as hall_id',
                    'halls.hall_no as hall_no',
                    'halls.hall_name as hall_name',
                    'halls.capacity as hall_capacity',

                    'hall_allocations.id as hall_allocation_id',
                    'hall_allocations.note as hall_allocation_note',

                    'batch_types.id as hall_type_id',
                    'batch_types.title as hall_type_title',

                    'hall_categories.id as hall_category_id',
                    'hall_categories.title as hall_category_title',

                    'lecture_schedules.id as schedule_id',
                    'lecture_schedules.from as from',
                    'lecture_schedules.to as to',
                    'lecture_schedules.no_of_students as no_of_students',
                    'lecture_schedules.date as date',
                    'lecture_schedules.status as status',

                    'modules.id as module_id',
                    'modules.title as module_title',
                    'modules.description as module_description',
                    'modules.code as module_code',

                    'lecturers.id as lecturer_id',
                    'lecturers.full_name as lecturer_name',
                    'lecturers.nibm_id as lecturer_nibm_id',
                    'lecturers.email as lecturer_email',
                    'lecturers.contact_no as lecturer_contact_no'
                    )
                ->paginate($request->input('page_size'));
            return \Response::json(
                [
                    'error' => false,
                    'hall' => $halls,
                    'date'=>$date,
                    'status_code' => 200
                ], 200);
        }
    }

    public function show($id){
        $validator = Validator::make(['hall_id'=>$id],['hall_id'=>['required','exists:halls,id']]);

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $halls= Hall::findorfail($id)->first();
            $hall_type = $halls->halltype()->first();
            $category = $halls->hall_category()->first();
            return \Response::json(
                [
                    'error'=> false,
                    'hall' => $halls,
                    'type'=> $hall_type,
                    'category'=>$category,
                    'status_code' => 200
                ], 200);
        }
    }

    public function getAll(Request $request){
        $validator = Validator::make(['page_size'=>$request->input('page_size')], ['page_size'=>['required','integer']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $halls = Hall::leftjoin('batch_types','halls.hall_type','=','batch_types.id')
                ->leftjoin('hall_categories','halls.category','=','hall_categories.id')
                ->select(
                    'halls.*',

                    'batch_types.id as hall_type_id',
                    'batch_types.title as hall_type_title',
                    'batch_types.description as hall_type_description',

                    'hall_categories.id as hall_category_id',
                    'hall_categories.title as hall_category_title',
                    'hall_categories.description as hall_category_description'
                    )
                ->paginate($request->input('page_size'));
            return \Response::json(
                [
                    'error' => false,
                    'halls' => $halls,
                    'status_code' => 200
                ], 200);
        }
    }

    public function getAllHallAllocations(Request $request){
        $validator = Validator::make(['page_size'=>$request->input('page_size')], ['page_size'=>['required','integer']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $hall_type =0;
            $date = 0;
            if($request->filled('hall_type')){
                $hall_type = $request->input('hall_type');
            }
            if($request->filled('date')){
                $date = Carbon::create($request->input('date'));
//                $date = $request->input('date');
            }

            $halls = Hall::leftjoin('hall_allocations','halls.id','=','hall_allocations.hall_id')
                ->leftjoin('lecture_schedules','hall_allocations.schedule_id','=','lecture_schedules.id')
                ->leftjoin('lecturers','lecture_schedules.lecturer_id','=','lecturers.id')
                ->leftjoin('modules','lecture_schedules.module_id','=','modules.id')
                ->leftjoin('hall_categories','halls.category','=','hall_categories.id')
                ->leftjoin('batch_types','halls.hall_type','=','batch_types.id')
                ->when($request->filled('hall_type'), function ($query) use ($hall_type) {
                    return $query->where('batch_types.id', $hall_type);
                })
                ->when($request->filled('date'), function ($query) use ($date) {
                    return $query->where('lecture_schedules.date','=', $date);
                })
                ->select(
                    'halls.id as hall_id',
                    'halls.hall_no as hall_no',
                    'halls.hall_name as hall_name',
                    'halls.capacity as hall_capacity',

                    'hall_allocations.id as hall_allocation_id',
                    'hall_allocations.schedule_id as schedule_id',
                    'hall_allocations.note as hall_allocation_note',

                    'batch_types.id as hall_type_id',
                    'batch_types.title as hall_type_title',

                    'hall_categories.id as hall_category_id',
                    'hall_categories.title as hall_category_title',

                    'lecture_schedules.id as schedule_id',
                    'lecture_schedules.from as from',
                    'lecture_schedules.to as to',
                    'lecture_schedules.no_of_students as no_of_students',
                    'lecture_schedules.date as date',
                    'lecture_schedules.status as status',

                    'modules.id as module_id',
                    'modules.title as module_title',
                    'modules.description as module_description',
                    'modules.code as module_code',

                    'lecturers.id as lecturer_id',
                    'lecturers.full_name as lecturer_name',
                    'lecturers.nibm_id as lecturer_nibm_id',
                    'lecturers.email as lecturer_email',
                    'lecturers.contact_no as lecturer_contact_no'
                    )
                ->paginate($request->input('page_size'));
            return \Response::json(
                [
                    'error' => false,
                    'hall' => $halls,
                    'date'=>$date,
                    'status_code' => 200
                ], 200);
        }
    }


}
