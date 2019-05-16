<?php

namespace App\Http\Controllers;

use App\Models\Messenger;
use App\Models\Project;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    public function index(Request $request){
        //only for admin and manager
        if(!$request->user()->hasAnyRole(['admin','manager','client'])){
            return response()->json(array_merge([
                'success'=>false],__('messages.role_error')),403);
        }
        $messengers = Messenger::query()
            ->get();

        return response()->json([
            'Messengers'=>$messengers,
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