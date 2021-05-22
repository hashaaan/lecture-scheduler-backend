<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user/create','UserController@create');

Route::post('/lectureschedule/create','LectureScheduleController@create');

Route::get('lectureschedule/filter','LectureScheduleController@filter');

Route::get('lectureschedule/{id}','LectureScheduleController@show');

Route::delete('/lectureschedule/{id}','LectureScheduleController@delete');

Route::post('/batch/create','BatchController@create');

Route::delete('/batch/{id}','BatchController@delete');

Route::get('/batchtypes',function () {
    return \App\Http\Resources\BatchTypesResource::collection(\App\BatchType::all());
});

Route::get('batch/getbytype/{batch_type_id}','BatchController@showByType');

Route::get('/batches',function () {
    return \App\Http\Resources\BatchResource::collection(\App\Batch::all());
});

Route::get('batch/search','BatchController@search');

Route::get('batch/searchwithtype','BatchController@searchWithType');

Route::get('halls/getbytype/{type_id}','HallController@getHallsByType');

Route::get('batch/{batch_id}','BatchController@show');



Route::get('modules/active/search','ModuleController@searchActiveModules');

Route::get('modules/batches/{module_id}','ModuleController@getBatches');
Route::get('modules/lecturers/{module_id}','ModuleController@getLecturers');

Route::get('modules/active/{page_size}','ModuleController@getActiveModules');

Route::post('modules/create','ModuleController@create');

Route::delete('module/{module_id}','ModuleController@destroy');

Route::get('modules/lectureschedule','ModuleController@showSchedules');

Route::post('halls/create','HallController@create');

Route::get('halls/today','HallController@getTodaysHallAllocations');

Route::get('halls/allocations','HallController@getAllHallAllocations');

Route::get('halls','HallController@getAll');

Route::get('halls/{id}','HallController@show');
