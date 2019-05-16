<?php

namespace App\Http\Helpers\Telegram;

use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TelegramHelper
{

    public static function setWebHook($token)
    {
        try {
            return true;
            $url = "https://" . request()->getHttpHost() . "/api/telegram/" . $token;

            $telegram = new Api($token);
            $result = $telegram->setWebhook([
                "url" => $url,
                'content-type' => 'application/json'
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
