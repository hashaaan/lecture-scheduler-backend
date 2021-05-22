<?php

namespace App\Http\Controllers;

use App\Batch;
use App\BatchModule;
use App\Http\Resources\LectureScheduleCollection;
use App\Http\Resources\ModuleCollection;
use App\Http\Resources\ModuleResource;
use App\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ModuleController extends Controller
{
    public function getActiveModules($page_size){
        $validator = Validator::make(['page_size'=>$page_size],['page_size'=>['required','integer']]);
        if($validator->fails()) {
            return \Response::json(
                [
                    'error' => true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $modules = Module::where('status','=',1)->paginate($page_size);
            return \Response::json(
                [
                    'error'=> false,
                    'modules' => $modules,
                    'status_code' => 200
                ], 200);
        }
    }

    public function searchActiveModules(Request $request){
        $validator = Validator::make(['search_term'=>$request->input('search_term'),'page_size'=>$request->input('page_size'),'all'=>$request->input('all')],
            [
                'search_term'=> 'required',
                'page_size'=>['required','integer'],
                'all'=>['required','boolean']
            ]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $modules = [];
            if($request->input('all')){
                $modules = Module::where('status','=',1)->orderby('title')->paginate($request->input('page_size'));
            }else{
                $search_term = $request->input('search_term');
                $modules = Module::where('status','=',1)
                    ->where(function($query) use ($search_term){
                        $query->where('title','like','%'.$search_term.'%')
                            ->orWhere('code','like','%'.$search_term.'%');
                    })
                    ->orderby('title')->paginate($request->input('page_size'));
            }
            return \Response::json(
                [
                    'error'=> false,
                    'modules' => $modules,
                    'status_code' => 200
                ], 200);
        }
    }

    public function getBatches($module_id){
        $validator = Validator::make(['module_id'=>$module_id],['module_id'=>['required','exists:modules,id','exists:batch_modules,module_id']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $batches = \DB::table('batches')
                ->join('batch_modules',function ($join){
                    $join->on('batch_modules.batch_id','=','batches.id');
                })
                ->where('batch_modules.module_id','=',$module_id)
                ->where('batches.status','=','active')
                ->orderby('batch_number')
                ->select('batches.*')
                ->groupBy("batches.id")
                ->get();
            return \Response::json(
                [
                    'error'=> false,
                    'modules' => $batches,
                    'status_code' => 200
                ], 200);
        }
    }

    public function getLecturers($module_id){
        $validator = Validator::make(['module_id'=>$module_id],['module_id'=>['required','exists:modules,id','exists:batch_modules,module_id']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else{
            $batches = \DB::table('lecturers')
                ->join('batch_modules',function ($join){
                    $join->on('batch_modules.lecturer','=','lecturers.id');
                })
                ->where('batch_modules.module_id','=',$module_id)
                ->where('lecturers.status','=','active')
                ->orderby('lecturers.full_name')
                ->select("lecturers.*")
                ->groupBy("lecturers.id")
                ->get();
            return \Response::json(
                [
                    'error'=> false,
                    'modules' => $batches,
                    'status_code' => 200
                ], 200);
        }
    }

    public function create(Request $request){
        $validator = Validator::make($request->all(),[
            'title'=>['required','String'],
            'description'=>['String'],
            'theory_hours'=>['required','integer'],
            'practical_hours'=>['required','integer'],
            'status'=>['required',Rule::in([1,0])],
            'code'=>['required','unique:modules,code'],
            'lecturers'=>['required','array'],
            'lecturers.*.lecturer_id'=>['required','exists:lecturers,id'],
            'batches'=>['required','array'],
            'batches.*.batch_id'=>['required','exists:batches,id']
        ]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $module_data = [
                'title'=>$request->title,
                'description'=>$request->description,
                'theory_hours'=>$request->theory_hours,
                'practical_hours'=>$request->practical_hours,
                'status'=>$request->status,
                'code'=>$request->code
            ];
            $module = Module::create($module_data);
            $batches = $request->batches;
            $lecturers = $request->lecturers;
            foreach ($lecturers as $lecturer)
            {
                foreach ($batches as $batch)   {
                    $module->batches()->create(['batch_id'=>$batch['batch_id'],'lecturer'=>$lecturer['lecturer_id']]);
                }
            }
            $module_j = new ModuleResource($module);
            return \Response::json(
                [
                    'error'=> false,
                    'response' => $module_j ,
                    'status_code' => 200
                ], 200);
        }
    }

    public function destroy($module_id){
        $validator = Validator::make(['module_id'=>$module_id],['module_id'=>['required','exists:modules,id']]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $module = Module::findorfail(['id'=>$module_id])->first();
            $batch_mods = $module->batches()->delete();
            $schedules = $module->lectureschedules()->delete();
            $deleted = $module->delete();
            return \Response::json(
                [
                    'error'=> false,
                    'deleted_items' => $deleted,
                    'batch_allocations'=>$batch_mods,
                    'schedules'=>$schedules,
                    'status_code' => 200
                ], 200);
        }
    }

    public function showSchedules(Request $request)
    {
        $validator = Validator::make(['module_id'=>$request->input('module_id'),'page_size'=>$request->input('page_size')],
            ['module_id'=>['required','exists:modules,id'],
             'page_size'=>['required','integer']
            ]);
        if($validator->fails()){
            return \Response::json(
                [
                    'error'=>true,
                    'response' => $validator->errors(),
                    'status_code' => 400
                ], 400);
        }else {
            $module = Module::findorfail(['id'=>$request->input('module_id')])->first();
//            $schedules = $module->lectureschedules()->paginate($request->input('page_size'));

          $schedules = $module->lectureschedules()->paginate($request->input('page_size'));
          $schedules_c = new LectureScheduleCollection($schedules);
            return \Response::json(
                [
                    'error'=> false,
                    'schedules'=>$schedules_c,
                    'status_code' => 200
                ], 200);
        }
    }

}
