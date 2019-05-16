<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $permissions = Permission::query()->get();
        return response()->json([
            'Permissions'=>$permissions,
            'success'=>true
        ],200);
    }

    public function store(){
    }

    public function update(){
    }

    public function destroy(){
    }
}