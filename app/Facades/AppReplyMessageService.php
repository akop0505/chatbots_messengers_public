<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class AppReplyMessageService
 * @package App\Facades
 */
class AppReplyMessageService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'appReplyMessage';
    }
}