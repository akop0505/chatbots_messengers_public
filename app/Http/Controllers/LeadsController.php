<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadsRequest\StoreLeadRequest;
use App\Http\Requests\LeadsRequest\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadsStatusDict;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class LeadsController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;

        $leadQuery = Lead::query()->whereHas('project')->with(['owner:id,first_name,last_name','project:id,name','status:id,name','passage.actions']);
        if($request->user()->hasRole('client')){
            $leadQuery->whereIn('project_id', $request->user()->project->pluck('id')->toArray());
        } else if($request->user()->hasRole('manager')){
            $leadQuery->whereIn('project_id', $request->user()->parent->project->pluck('id')->toArray());
        }
        $totalRows = $leadQuery->count();
        $leads = $leadQuery->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'Leads'=>$leads,
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
        $lead =  Lead::query()
            ->with(['owner:id','owner.project:id,name'])
            ->where(['id'=>$id])
            ->first();
        $response = [];
        if($lead){
            $response = $lead->toArray();
            //unset($response['project_messengers']);
        }

        return response()->json([
            'Lead'=>$response,
            'success'=>true
        ],200);
    }

    public function store(StoreLeadRequest $request) {
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $data = $request->all();

        try{
            DB::beginTransaction();
            $lead = new Lead();
            $lead->fill($data);

            $lead->save();

            DB::commit();
            return response()->json([
                'Lead'=>$lead->toArray(),
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

    public function update($id, UpdateLeadRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id) || $request->user()->hasRole('manager') && !$request->user()->parent()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $lead = Lead::findOrFail($id);

            $lead->fill($request->all());
            $lead->save();
            DB::commit();

            $leadResult = $lead->toArray();

            return response()->json([
                'Lead'=>$leadResult,
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
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id) || $request->user()->hasRole('manager') && !$request->user()->parent()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $lead = Lead::findOrFail($id);

            $lead->delete();
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

    public function getStatuses(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $statuses = LeadsStatusDict::query()->get();
        return response()->json([
            'Statuses'=>$statuses,
            'success'=>true
        ],200);
    }
}