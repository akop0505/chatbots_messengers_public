<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @SWG\Swagger(
 *   basePath="/api/",
 *     schemes={"http","https"},
 *     @SWG\SecurityScheme(
 *         securityDefinition="Bearer",
 *         type="apiKey",
 *         name="Authorization",
 *         in="header"
 *     ),
 *   @SWG\Info(
 *     title="5m3 API",
 *     version="1.0.0"
 *   )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
