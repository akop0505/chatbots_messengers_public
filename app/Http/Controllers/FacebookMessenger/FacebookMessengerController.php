<?php

namespace App\Http\Controllers\FacebookMessenger;

use App\Http\Controllers\Controller;
use App\Models\AnswerableMessage;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use AppFacebookService;
use AppReplyMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class FacebookMessengerController extends Controller
{
    public function __construct(
        AnswerableMessageRepository $answerableMessageRepository,
        GatewaySettingsRepository $gatewaySettingsRepository
    )
    {
        $this->gatewaySettingsModel = $gatewaySettingsRepository;
        $this->answerableMessageModel = $answerableMessageRepository;
    }

    public function validateWebHook(Request $request)
    {
        Log::debug('Facebook Messenger : ', $request->all());
        return $request->get('hub_challenge');
    }

    public function catchMessage(Request $request)
    {
        $data = $request->all();
        Log::debug('Facebook Messenger  $data', [$data]);
        $appId = $request->route('appId');
        if ($data['object'] !== 'page') {
            return;
        }
        $messagingObject = AppFacebookService::getMessaging($data);
        if(!isset($messagingObject['message']) || !isset($messagingObject['message']['text'])){
            Log::debug("message.text Not isset",[$data]);
            return;
        }
        if ($messagingObject && !$messagingObject['is_bot']) {
            AppFacebookService::subscribe($appId, $messagingObject);
            $replyMessage = AppReplyMessageService::usersFbAnswer($messagingObject, $appId);
            if (!$replyMessage) {
                $model = [
                    "answer" => $messagingObject['message']['text'],
                    "chat_id" => $messagingObject['sender']['id'],
                    "chat_user_id" => $messagingObject['sender']['id'],
                    "bot_username" => $appId
                ];
                $this->answerableMessageModel->createSimpleMessage($model, AnswerableMessage::CHANNEL_FB);
                return;
            }
            Log::debug('Facebook Messenger  $subscribed ', [$replyMessage]);
            $gatewaySettings = $this->gatewaySettingsModel->getFbSettingsByAppId($appId);
            Log::debug('Facebook Messenger  gatewaySettings ', [$gatewaySettings]);

            $response = AppFacebookService::sendToMember($gatewaySettings['field_value'], $messagingObject['sender']['id'], $replyMessage['text']);
            if ($response['message_id']) {
                if (isset($replyMessage['shouldSave']) && $replyMessage['shouldSave'] === true) {
                    $model = [
                        "message_id" => "m_".$response['message_id'],
                        "template_id" => $replyMessage['template_id'],
                        "receiver" => $messagingObject['sender']['id'],
                        "appId" => $appId
                    ];
                    $this->answerableMessageModel->createFbAnswerableQuestion($model);
                } else {
                    $botSimple = [
                        "answer" => $replyMessage['text'],
                        "chat_id" => $messagingObject['sender']['id'],
                        "bot_username" => $appId
                    ];
                    $this->answerableMessageModel->createSimpleMessage($botSimple, AnswerableMessage::CHANNEL_FB, AnswerableMessage::BOT_SIMPLE);
                }
            }
        } elseif ($messagingObject && $messagingObject['is_bot']) {
            $botSimple = [
                "answer" => $messagingObject['message']['text'],
                "chat_id" => $messagingObject['recipient']['id'],
                "bot_username" => $appId,
                "message_id"=>"m_".$messagingObject['message']['mid']
            ];
            $this->answerableMessageModel->createSimpleMessage($botSimple, AnswerableMessage::CHANNEL_FB, AnswerableMessage::BOT_SIMPLE);
        }
        Log::debug('Facebook Messenger  ECHO: ', [$messagingObject]);
    }
}