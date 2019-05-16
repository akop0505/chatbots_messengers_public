<?php

namespace App\Http\Controllers;

use App\Http\Resources\Timezones\TimezoneCollection;
use Illuminate\Http\Request;

class TimezonesController extends Controller
{
    public function index(Request $request){
        return response()->json([
            'Timezones'=> TimezoneCollection::generate(),
            'success'=>true
        ],200);
    }

}