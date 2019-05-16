<?php

namespace App\Http\Controllers;

use App\Models\Language;

class LanguageController extends Controller
{
    public function index(){
        $languages = Language::select(['code','name'])->orderBy('code')->get();
        return response()->json([
            'Languages'=>$languages,
            'success'=>true
        ],200);
    }
}