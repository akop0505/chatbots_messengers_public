<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiverRequest\StoreReceiverRequest;
use App\Http\Requests\ReceiverRequest\UpdateReceiverRequest;
use App\Http\Resources\Receivers\ReceiverCollection;
use App\Models\Receiver;
use Illuminate\Http\Request;

class ReceiverController extends Controller
{
    /**
     *
     * get all receivers using pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $receiverQuery = Receiver::query()
            ->with(['project'])
            ->orderBy('project_id');

        if($request->user()->hasRole('client')){
            $receiverQuery
                ->select(['receivers.*'])
                ->join('projects','projects.id','=','receivers.project_id')->where(['client_id'=>$request->user()->id]);
        }

        $totalRows = $receiverQuery->count();
        $receivers = $receiverQuery
            ->offset($page*$limit)
            ->limit($limit)
            ->get();

        return response()->json([
            'Receivers'=>new ReceiverCollection($receivers),
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    /**
     *
     * get the receiver by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        $receiver = Receiver::findOrFail($id);

        if($request->user()->hasRole('client') && !$request->user()->ownProject($receiver->project_id)){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        return response()->json([
            'Receiver'=>$receiver,
            'success'=>true
        ],200);
    }

    /**
     *
     * create a new receiver
     *
     * @param StoreReceiverRequest $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(StoreReceiverRequest $request) {
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        if($request->user()->hasRole('client') && !$request->user()->ownProject($request->get('project_id'))){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $data = $request->all();
            $receiver = new Receiver();
            $receiver->fill($data);
            $receiver->save();
            return response()->json([
                'Receiver'=>$receiver,
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

    /**
     *
     * update the receiver by id
     *
     * @param $id
     * @param UpdateReceiverRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id,UpdateReceiverRequest $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $data = $request->all();
            $receiver = Receiver::findOrFail($id);
            if($request->user()->hasRole('client') && !($request->user()->ownProject($data['project_id']) && $request->user()->ownProject($receiver->project_id))){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }

            $receiver->fill($data);
            $receiver->save();
            return response()->json([
                'Receiver'=>$receiver,
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

    /**
     *
     * delete the receiver by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $receiver = Receiver::findOrFail($id);
            if($request->user()->hasRole('client') && !$request->user()->ownProject($receiver->project_id)){
                return response()->json(array_merge([
                    'success'=>false],__('messages.role_error')),403);
            }
            $receiver->delete();
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiversToSelect(Request $request) {
        $project_id  = $request->get('project_id');
        $receiversQ = Receiver::query()->select(['id','name']);
        if(isset($project_id) && !empty($project_id)){
            $receiversQ->where(['project_id'=>$project_id]);
        }
        $receivers = $receiversQ->get();
        return response()->json([
            'Receivers'=>$receivers,
            'success'=>true
        ],200);
    }
}