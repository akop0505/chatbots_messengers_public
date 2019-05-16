<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;

class ValidProjectKey
{
    public function __construct(UserRepository $userRepository)
    {
        $this->userModel = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $project = $this->userModel->getProjectByAccessKey($request->get('project_key'));
        if(!$project){
            return response()
                ->json([
                    "errorCode"=>"INVALID_PROJECT_KEY",
                    "errors"=>[
                        "Ключ проекта неверный или заблокированный."
                    ]
                ],401,[]);
        }
        return $next($request);
    }
}
