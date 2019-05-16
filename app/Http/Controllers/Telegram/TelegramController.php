<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Services\AppTelegram;
use App\Models\AnswerableMessage;
use App\Models\GatewaySetting;
use App\Models\MenuItem;
use App\Models\PresentReply;
use App\Models\ProjectMessenger;
use App\Models\Template;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\TemplateRepository;
use function foo\func;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use phpseclib\Crypt\DES;
use Telegram;
use Telegram\Bot\Api;
use AppTelegramService;
use AppReplyMessageService;

/**
 * Class TelegramController
 * @package App\Http\Controllers\Telegram
 */
class TelegramController extends Controller
{
    public function __construct(
        GatewaySettingsRepository $gatewaySettingsRepository,
        AnswerableMessageRepository $answerableMessageRepository,
        TemplateRepository $templateRepository
    )
    {
        $this->gatewaySettingsModel = $gatewaySettingsRepository;
        $this->answerableMessageModel = $answerableMessageRepository;
        $this->templateModel = $templateRepository;
    }

    public function setWebHook(Request $request){
        $data = $request->all();
        $result = AppTelegramService::setWebHook($data['token'],$data['origin']);
        return response()->json(["result"=>$result]);
    }

    public function telegramCatchMessage(Request $request){
        Log::debug("TELEGRAM MESSAGE",$request->all());
        $requestData = $request->all();
        if(isset($requestData['message']['text'])){
            if(AppTelegramService::isBotCommand()) {
                AppTelegramService::commandHandler();
            }else{
                $replyData = [
                    "chat_id"=>$request->get('message')['chat']['id'],
                    "text"=>"Неизвестное сообщение "."\"".$request->get('message')['text']."\"",
                ];
                $replyMessage = AppReplyMessageService::usersTelegramAnswer($request->get('message'),$request->route('token'));
                if($replyMessage){
                    $result = AppTelegramService::replyMessage($replyData['chat_id'],$replyMessage);
                    if($result && (isset($replyMessage['shouldSave']) && $replyMessage['shouldSave'] === true)){
                        $answerableQuestion = [
                            "message_id"=>$result->getMessageId(),
                            "template_id"=> $replyMessage['template_id'],
                            "template_params"=> json_encode($replyMessage['template_params']),
                            "chat_id"=>$request->get('message')['chat']['id'],
                            "chat_user_id"=>$request->get('message')['chat']['id'],
                            "bot_username"=>AppTelegramService::getBotUsername(),
                            "message_params"=>"" // Пока что не ясно что здесь хранить
                        ];
                        $this->answerableMessageModel->createTelegramAnswerableQuestion($answerableQuestion);
                    }else{
                        $botSimple = [
                            "answer"=>$replyMessage['text'],
                            "chat_id"=>$request->get('message')['chat']['id'],
                            "chat_user_id"=>$request->get('message')['chat']['id'],
                            "bot_username"=>AppTelegramService::getBotUsername()
                        ];
                        $this->answerableMessageModel->createSimpleMessage($botSimple,AnswerableMessage::CHANNEL_TELEGRAM, AnswerableMessage::BOT_SIMPLE);
                    }
                }else{
                    $answerableQuestion = [
                        "answer"=>$request->get('message')['text'],
                        "chat_id"=>$request->get('message')['chat']['id'],
                        "chat_user_id"=>$request->get('message')['chat']['id'],
                        "bot_username"=>AppTelegramService::getBotUsername()
                    ];
                    $this->answerableMessageModel->createSimpleMessage($answerableQuestion,AnswerableMessage::CHANNEL_TELEGRAM, AnswerableMessage::USER_SIMPLE);
                }
            }
        }else{
            $messageType = AppTelegramService::getMessageType($requestData['message']);
            if($requestData){
                if(!isset($requestData['message'][$messageType]['file_id'])){
                    return;
                }
                $filePath =  AppTelegramService::getFilePath($requestData['message'][$messageType]['file_id']);
                $messageData = [
                    "file_json"=>json_encode($requestData['message'][$messageType]),
                    "file_path"=>$filePath,
                    "bot_username"=>AppTelegramService::getBotUsername(),
                    "channel"=>AnswerableMessage::CHANNEL_TELEGRAM,
                    "message_id"=>$requestData['message']['message_id'],
                    "chat_id"=>$requestData['message']['chat']['id'],
                    "state"=>AnswerableMessage::USER_SIMPLE,
                    "type"=>AnswerableMessage::TYPE_FILE
                ];
                $answerableMessage = AnswerableMessage::create($messageData);
            }
        }
    }
}
