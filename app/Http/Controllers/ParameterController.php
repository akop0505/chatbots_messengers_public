<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParameterRequest\StoreParameterRequest;
use App\Models\Parameter;
use Illuminate\Http\Request;

class ParameterController extends Controller
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

        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $parametersQuery = Parameter::query()
            ->where(['project_id'=>$project_id]);

        //total rows count
        $totalRows = $parametersQuery->count();

        $parameters = $parametersQuery
            ->offset($page*$limit)
            ->limit($limit)
            ->get();

        return response()->json([
            'Parameters'=>$parameters,
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    public function show($id,Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $parameter = Parameter::findOrFail($id);

        if($request->user()->hasRole('client') && !$request->user()->ownProject($parameter->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        //$parameter->variable = preg_replace('/[\{\}]+/','',$parameter->variable);
        return response()->json([
            'Parameter'=>$parameter,
            'success'=>true
        ],200);
    }

    public function store(StoreParameterRequest $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            if($request->user()->hasRole('client') && !$request->user()->ownProject($data['project_id'])){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }
            $parameter = new Parameter();
            $parameter->fill($data);
            $parameter->variable = "{".$parameter->variable."}";
            $parameter->save();
            return response()->json([
                'Parameter'=>$parameter,
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

    public function update($id,StoreParameterRequest $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $parameter = Parameter::findOrFail($id);
            if($request->user()->hasRole('client') && !$request->user()->ownProject($parameter->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $parameter->fill($data);
            $parameter->variable = "{".$parameter->variable."}";
            $parameter->save();
            return response()->json([
                'Parameter'=>$parameter,
                'success'=>true
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function destroy($id, Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $parameter = Parameter::findOrFail($id);

            if($request->user()->hasRole('client') && !$request->user()->ownProject($parameter->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $parameter->delete();
            return response()->json([
                'id'=>$id,
                'success'=>true
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }

    public function parametersToSelect(Request $request) {
        $project_id  = $request->get('project_id');
        $parametersQ = Parameter::query()->select(['id','variable','name']);
        if(isset($project_id) && !empty($project_id)){
            $parametersQ->where(['project_id'=>$project_id]);
        }
        $parameters = $parametersQ->get();
        return response()->json([
            'Parameters'=>$parameters,
            'success'=>true
        ],200);
    }
}