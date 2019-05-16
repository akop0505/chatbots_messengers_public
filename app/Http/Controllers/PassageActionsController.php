<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadsRequest\StoreLeadRequest;
use App\Http\Requests\LeadsRequest\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadsStatusDict;
use App\Models\PassagesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class PassageActionsController extends Controller
{
    public function index(Request $request){

    }

    public function show($id,Request $request){

    }

    public function store(StoreLeadRequest $request) {

    }

    public function update($id, UpdateLeadRequest $request) {

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
            $lead = PassagesAction::findOrFail($id);

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

}