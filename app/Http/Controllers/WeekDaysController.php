<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParameterRequest\StoreWeekDaysRequest;
use App\Models\Project;
use App\Models\WeekDay;
use Illuminate\Http\Request;

class WeekDaysController extends Controller
{
    public function index(Request $request){
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $project_id = $request->get('project_id');

        if(!isset($project_id) || empty($project_id)){
            return response()->json([
                'success'=>false,
                'errors'=>['The project_id is required']
            ],200);
        }
        if($request->user()->hasRole('client') && !$request->user()->ownProject($project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $query = WeekDay::query()
            ->where(['project_id'=>$project_id])
            ->get();

        return response()->json([
            'WeekDays'=>$query,
            'success'=>true
        ],200);
    }

    public function store(StoreWeekDaysRequest $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $project_id = $data[0]['project_id'];

            if($request->user()->hasRole('client') && !$request->user()->ownProject($project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $oldWeekDays = Project::find($project_id)->weekDays;

            foreach ($data as $dayData){
                $storedWeekDay = $oldWeekDays->search(function ($item, $key) use ($dayData) {
                    return $item->order === $dayData['order'];
                });

                if($storedWeekDay !== false){
                    $day = $oldWeekDays[$storedWeekDay];
                } else {
                    $day = new WeekDay();
                }
                $day->fill($dayData);
                $day->save();
            }

            return response()->json([
                'success'=>true
            ],200);
        }catch ( \Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}