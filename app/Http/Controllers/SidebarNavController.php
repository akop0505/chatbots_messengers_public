<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SidebarNav;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use DB;

class SidebarNavController extends Controller
{
    public function index(Request $request){
        //only for admin
        if(!$request->user()->hasAnyRole(['admin'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $sidebarNavs = SidebarNav::query()
            ->with(['roles'])->get();
        return response()->json([
            "success"=>true,
            "SidebarNavs"=>$sidebarNavs
        ]);
    }

    public function show($id,Request $request) {
        if(!$request->user()->hasAnyRole(['admin'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $sidebarNav = SidebarNav::query()
            ->where(['id'=>$id])
            ->with(['roles'])->first();
        return response()->json([
            "success"=>true,
            "SidebarNav"=>$sidebarNav
        ]);
    }

    public function update($id, Request $request) {
        //only for admin
        if(!$request->user()->hasRole('admin')){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $sidebarNav = SidebarNav::findOrFail($id);
        $roles = $request->get('Roles');

        try{
            DB::beginTransaction();
            $sidebarNav->roles()->sync($roles);
            DB::commit();

            return response()->json([
                'SidebarNav'=>$sidebarNav,
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

    public function getWithAccessRoles(Request $request){
        $sidebarNavs = SidebarNav::all();
        $result = [];
        $parent = $request->user()->parent;
        $isClient = false;
        if($parent && $parent->role->name == 'client'){
           $isClient = true;
        }
        foreach ($sidebarNavs as $sidebarNav){
            $result[$sidebarNav->name] = $sidebarNav->roles->map(function($item) use($sidebarNav,$isClient){
                /*if($sidebarNav->name == 'user' && $isClient && $item->name =='client'){
                    return;
                }*/
                return $item->name;
            });
            if($sidebarNav->name == 'user' && $isClient){
                $result[$sidebarNav->name] = $result[$sidebarNav->name]->filter(function($t){
                    return $t !== 'client';
                });
            }
        }
        return response()->json([
            "success"=>true,
            "SidebarNavs"=>$result
        ]);
    }

    public function getWithNotAccessRoles(){
        $roles = Role::all();
        $sidebarNavs = SidebarNav::all();
        $result = [];
        foreach ($sidebarNavs as $sidebarNav){
            $result[$sidebarNav->name] = $roles->diff($sidebarNav->roles)->map(function($item){return $item->name;});
        }

        return response()->json([
            "success"=>true,
            "SidebarNavs"=>$result
        ]);
    }
}