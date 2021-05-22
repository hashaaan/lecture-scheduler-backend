<?php

namespace App\Http\Controllers;

use App\Batch;
use App\BatchType;
use App\Http\Resources\BatchCollection;
use App\Http\Resources\BatchResource;
use App\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(),[
                'data' => ['required','array'],
                'data.batch_number'=>['required','string','max:100','unique:batches,batch_number'],
                'data.description'=>['present','nullable','string','max:400'],
                'data.batch_type'=>['required','exists:batch_types,id'],
                'data.no_of_students'=>['required','Integer'],
                'data.name'=>['required','string','max:200'],
                'data.start_date'=>['required','date_format:Y/m/d'],
                'data.end_date'=>['required','date_format:Y/m/d'],
                'data.consultant_id'=>['required','exists:lecturers,id'],
                'data.status'=>['required',Rule::in(['active', 'inactive'])],
            ]
        );

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }
        else{
            $data = $request->data;
            $batch = [
                        'batch_number'=>$data["batch_number"],
                        'description'=>$data["description"],
                        'batch_type'=>$data["batch_type"],
                        'no_of_students'=>$data["no_of_students"],
                        'name'=>$data["name"],
                        'start_date'=>$data["start_date"],
                        'end_date'=>$data["end_date"],
                        'lecturer_id'=>$data["consultant_id"],
                        'status'=>$data["status"]
                     ];
            $created = Batch::create($batch);
            $batch_type = BatchType::findOrFail(['id'=>$batch["batch_type"]]);
            $lecturer = Lecturer::findOrFail(['id'=>$batch['lecturer_id']]);
            $created["batch_type"] = $batch_type;
            $created["consultant"] = $lecturer;
            return \Response::json(
            [
                'error'=> false,
                'response' => $created ,
                'status_code' => 200
            ], 200);
        }
    }

    public function delete($id){
        $validator = Validator::make(['id'=>$id],[ 'id' => ['required','exists:batches,id']]);

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $deleted = Batch::destroy(['id'=>$id]);
            return \Response::json(
            [
                'error'=> false,
                'deleted_items' => $deleted,
                'status_code' => 200
            ], 200);
        }

    }

    public function show($batch_id){
        $validator = Validator::make(['batch_id'=>$batch_id],['batch_id'=>['required','exists:batches,id']]);

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $batch = Batch::findorfail(['id'=>$batch_id])->first();
            $batch_j = new BatchResource($batch);
            return \Response::json(
                [
                    'error'=> false,
                    'batches' => $batch_j,
                    'status_code' => 200
                ], 200);
        }
    }

    public function showByType($batch_type_id){
        $validator = Validator::make(['batch_type_id'=>$batch_type_id],['batch_type_id'=>['required','exists:batch_types,id']]);

        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
//            $batch_type = BatchType::findorfail(['id'=>$batch_type_id])->first();
//            $batches = $batch_type->batches()->paginate(10);
            $batches = Batch::where('batch_type','=',$batch_type_id)->paginate(10);
            $batches_j = new BatchCollection($batches);
            return \Response::json(
                [
                    'error'=> false,
                    'batches' => $batches_j,
                    'status_code' => 200
                ], 200);
        }
        return null;
    }

    public function searchWithType(Request $request){
        $validator = Validator::make(['search_term'=>$request->input('search_term'),'batch_type_id'=>$request->input('batch_type_id') ,'page_size'=>$request->input('page_size')],
                                    [
                                        'search_term'=> 'required',
                                        'batch_type_id'=>['required','exists:batch_types,id'],
                                        'page_size'=>['required','integer']
                                    ]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $search_term = $request->input('search_term');

            $batches = Batch::where('batch_type','=',$request->input('batch_type_id'))
                ->where(function($query) use ($search_term){
                    $query->where('Name','like','%'.$search_term.'%')
                        ->orWhere('batch_number','like','%'.$search_term.'%');
                })
                ->paginate($request->input('page_size'));

            $batches_j = new BatchCollection($batches);
            return \Response::json(
                [
                    'error'=> false,
                    'batches' => $batches_j,
                    'status_code' => 200
                ], 200);
        }
        return null;
    }

    public function search(Request $request){
        $validator = Validator::make(['search_term'=>$request->input('search_term'),'page_size'=>$request->input('page_size'), 'get_all'=> $request->input('get_all')],
            [
                'search_term'=> 'required',
                'page_size'=>['required','integer'],
                'get_all'=>['required','boolean'],
            ]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            if($request->input('get_all')){
                $batches = Batch::paginate($request->input('page_size'));
                $batches_j = new BatchCollection($batches);
                return \Response::json(
                    [
                        'error'=> false,
                        'batches' => $batches_j,
                        'status_code' => 200
                    ], 200);
            }
            $search_term = $request->input('search_term');

            $batches = Batch::where('Name','like','%'.$search_term.'%')
                ->orWhere('batch_number','like','%'.$search_term.'%')
                ->paginate($request->input('page_size'));
            $batches_j = new BatchCollection($batches);

            return \Response::json(
                [
                    'error'=> false,
                    'batches' => $batches_j,
                    'status_code' => 200
                ], 200);
        }
    }
}
