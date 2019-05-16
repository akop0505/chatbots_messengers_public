<?php

namespace App\Http\Controllers;

use App\Http\Requests\SequencesRequest\StoreSequenceRequest;
use App\Http\Requests\SequencesRequest\UpdateSequenceRequest;
use App\Http\Requests\SequencesRequest\SequenceTaskRequest;
use App\Models\Passages;
use App\Models\Sequence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class SequencesController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;

        $sequenceQuery = Sequence::query()->with('project:id,name')->has('project');
        if($request->user()->hasRole('client')){
            $sequenceQuery->whereIn('project_id', $request->user()->project->pluck('id')->toArray());
        } else if($request->user()->hasRole('manager')){
            $sequenceQuery->whereIn('project_id', $request->user()->parent->project->pluck('id')->toArray());
        }

        $totalRows = $sequenceQuery->count();
        $sequences = $sequenceQuery->offset($page*$limit)
            ->limit($limit)
            ->get();
        return response()->json([
            'Sequence'=>$sequences,
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
        $sequence =  Sequence::query()
            ->with('project')
            ->where(['id'=>$id])
            ->first();
        $response = [];
        if($sequence){
            $response = $sequence->toArray();
            //unset($response['project_messengers']);
        }

        return response()->json([
            'Sequence'=>$response,
            'success'=>true
        ],200);
    }

    public function store(StoreSequenceRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $data = $request->all();

        $responseData = [];
        try{
            DB::beginTransaction();
            $sequence = new Sequence();
            $sequence->fill($data);

            $sequence->save();

            $responseData["Sequence"] = $sequence->toArray();

            DB::commit();
            return response()->json([
                'Sequence'=>$responseData['Sequence'],
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

    public function update($id, UpdateSequenceRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $sequence = Sequence::findOrFail($id);

            $sequence->fill($request->all());
            $sequence->save();
            DB::commit();

            $sequenceResult = $sequence->toArray();

            return response()->json([
                'Sequence'=>$sequenceResult,
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

        if($request->user()->hasRole('client') && !$request->user()->ownProject($id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            DB::beginTransaction();
            $sequence = Sequence::findOrFail($id);

            $sequence->delete();
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

    public function start(Sequence $sequence, Request $request){
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $lead_ids = $request->get('leads');
        if( ! (is_array($lead_ids) && count($lead_ids) > 0) ){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        Passages::query()->where(['lead_id'=>$lead_ids])->with(['actions'])->get()->each(function($analytic) {
            $analytic->delete();
        });

        foreach ($lead_ids as $lead_id){
            (new Passages([
                'lead_id' => $lead_id,
                'sequence_id' => $sequence->id,
            ]))->save();
        }

        return response()->json([
            'Sequences'=>$lead_ids,
            'success'=>true
        ],200);
    }

    public function sequencesToSelect(Request $request){
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $query = Sequence::query()->select(['id','name'])->has('project');

        if($request->user()->hasRole('client')){
            $query->whereIn('project_id', $request->user()->project->pluck('id')->toArray());
        } else if($request->user()->hasRole('manager')){
            $query->whereIn('project_id', $request->user()->parent->project->pluck('id')->toArray());
        }

        return response()->json([
            'Sequences'=>$query->get(),
            'success'=>true
        ],200);
    }

    public function validateTaskOptions($type, SequenceTaskRequest $request){
        if(!$request->user()->hasAnyRole(['admin','client','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        return response()->json([
            'success'=>true
        ],200);
    }
}