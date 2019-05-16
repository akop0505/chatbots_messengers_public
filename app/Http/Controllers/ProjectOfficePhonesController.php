<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParameterRequest\StoreWeekDaysRequest;
use App\Http\Requests\ProjectOfficePhonesRequest\ProjectOfficePhonesRequest;
use App\Models\Project;
use App\Models\ProjectOfficePhone;
use App\Models\WeekDay;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectOfficePhonesController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin', 'client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;

        $project_id = $request->get('project_id');

        if($request->user()->hasRole('client') && !$request->user()->ownProject($project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $query = ProjectOfficePhone::query()->where(['project_id'=>$project_id]);
        $totalRows = $query->count();
        $phones = $query->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'ProjectOfficePhones'=>$phones,
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    public function show($id,Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $phone = ProjectOfficePhone::query()
            ->where(['id'=>$id])
            ->first();

        if($request->user()->hasRole('client') && !$request->user()->ownProject($phone->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $response = [];

        if($phone){
            $response = $phone->toArray();
            //unset($response['project_messengers']);
        }

        return response()->json([
            'ProjectOfficePhone'=>$response,
            'success'=>true
        ],200);
    }

    public function store(ProjectOfficePhonesRequest $request) {
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $data = $request->all();

        if($request->user()->hasRole('client') && !$request->user()->ownProject($data['project_id'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $phone = new ProjectOfficePhone();
            $phone->fill($data);

            $phone->save();

            DB::commit();
            return response()->json([
                'ProjectOfficePhone'=>$phone->toArray(),
                'success'=>true
            ],200);
        }catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function update($id, ProjectOfficePhonesRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $phone = ProjectOfficePhone::findOrFail($id);

        if($request->user()->hasRole('client') && !$request->user()->ownProject($phone->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();

            $phone->fill($request->all());
            $phone->save();
            DB::commit();

            $phoneResult = $phone->toArray();

            return response()->json([
                'ProjectOfficePhone'=>$phoneResult,
                'success'=>true
            ],200);

        }
        catch (ModelNotFoundException $e){
            DB::rollback();
            dd($e->getMessage());
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }


    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $phone = ProjectOfficePhone::findOrFail($id);

        if($request->user()->hasRole('client') && !$request->user()->ownProject($phone->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();

            $phone->delete();
            DB::commit();
            return response()->json([
                'id'=>$id,
                'success'=>true
            ],200);
        }
        catch (ModelNotFoundException $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    'No record found for id `'.$id.'`'
                ]
            ],400);
        }
        catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}