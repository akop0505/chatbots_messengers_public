<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppTelegramService
 * @package App\Facades
 */
class AppViberService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'appViber';
    }
}