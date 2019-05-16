<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolesRequest\StoreRolesRequest;
use App\Http\Requests\RolesRequest\UpdateRolesRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     *
     * get all roles using pagination
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $page = $request->get('page')?:0;
        $limit  = $request->get('limit')?:100;
        $roles = Role::query()
            ->offset($page*$limit)
            ->limit($limit)
            ->get();
        $totalRows = Role::count();
        return response()->json([
            'Roles'=>$roles,
            'totalRows'=>$totalRows,
            'success'=>true
        ],200);
    }

    /**
     *
     * get the role by id
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
        $role = Role::find($id);
        return response()->json([
            'Role'=>$role,
            'success'=>true
        ],200);
    }

    /**
     *
     * crete a new role
     *
     * @param StoreRolesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRolesRequest $request) {
        //only for admin
        if(!$request->user()->authorizeRoles('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $role = new Role();
            $role->fill($data);
            $role->save();
            return response()->json([
                'Role'=>$role,
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
     * update the role by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id,UpdateRolesRequest $request){
        //only for admin
        if(!$request->user()->authorizeRoles('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $data = $request->all();
            $role = Role::find($id);
            $role->fill($data);
            $role->save();
            return response()->json([
                'Role'=>$role,
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
     * delete the role by id
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id,Request $request){
        //only for admin
        if(!$request->user()->authorizeRoles('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        try{
            $role = Role::findOrFail($id);
            $role->delete();
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
}
