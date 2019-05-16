<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request){
        if(Auth::attempt(['username' => $request->get('username'), 'password' => $request->get('password')])){
            $user = Auth::user();
            $success['user'] =  $user;
            $success['user']['role'] =  $user->role;
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            return response()->json($success, 200);
        }
        else{
            return response()->json([
                'errorType'=>'INCORRECT_DATA_ERROR',
                'errors'=>[
                    'signin'=>'Не верная пара логина и пароля'
                ]
            ], 422);
        }
    }

    public function logout()
    {
        try{
            $user = Auth::user();
            $user->token()->revoke();
            $user->token()->delete();
            return response()->json([
                "success"=>true,
                "messages" =>"The user has been successfully logged out!"
            ],200);
        }
        catch (\Exception $e){
            return response()->json([
                'errorType'=>'GENERAL_ERROR',
                'errors'=>[
                    $e->getMessage()
                ]
            ],400);
        }
    }
}
