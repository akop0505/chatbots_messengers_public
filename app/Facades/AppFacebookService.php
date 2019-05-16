<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppTelegramService
 * @package App\Facades
 */
class AppFacebookService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'appFacebook';
    }
}