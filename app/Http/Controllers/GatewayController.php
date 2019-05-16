<?php

namespace App\Http\Controllers;

use App\Http\Requests\GatewaysRequest\StoreGatewayRequest;
use App\Http\Requests\GatewaysRequest\UpdateGatewayRequest;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;
use Psy\Util\Json;

class GatewayController extends Controller
{
    /**
     *
     * get all gateways using pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;

        $gateways = Gateway::getAll($page,$limit);
        $totalRows = Gateway::count();

        return response()->json([
            'Gateways'=>$gateways,
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    /**
     *
     * get gateway by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $gateway = Gateway::find($id);
            return response()->json([
                'Gateway'=>$gateway,
                'success'=>true
            ],200);
        }catch(\Exception $e){
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
     * create a new gateway
     *
     * @param StoreGatewayRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreGatewayRequest $request){
        //only for admin
        if(!$request->user()->authorizeRoles('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            if(!isset($data['config'])){
                $data['config'] = [];
            }
            $data['config'] = Json::encode($data['config']);

            $gateway = new Gateway();
            $gateway->fill($data);
            $gateway->save();

            return response()->json([
                'Gateway'=>$gateway,
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
     * update the gateway by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id,UpdateGatewayRequest $request){
        //only for admin
        if(!$request->user()->authorizeRoles('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }

        try{
            $data = $request->all();
            $gateway = Gateway::findOrFail($id);
            if(!isset($data['config'])){
                $data['config'] = [];
            }
            $data['config'] = Json::encode($data['config']);

            $gateway->fill($data);
            $gateway->save();
            return response()->json([
                'Event'=>$gateway,
                'success'=>true
            ],200);
        }catch (ModelNotFoundException $e){
            return response()->json([
                'success'=>false,
                'errors' =>[
                    'No record found for id `'.$id.'`'
                ]
            ],400);
        }
        catch (\Exception $e){
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
     * delete the gateway by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->hasRole('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $event = Gateway::findOrFail($id);
            $event->removed = 1;
            $event->save();

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
            return response()->json([
                'success'=>false,
                'errors' =>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}