<?php

namespace App\Http\Controllers\Viber;

use App\Http\Controllers\Controller;
use App\Models\AnswerableMessage;
use App\Models\GatewaySubscribe;
use App\Models\Template;
use App\Models\UsersChatInfo;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\UserChatInfoRepository;
use App\Services\AppViber;
use Illuminate\Http\Request;
use AppViberService;
use AppReplyMessageService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ViberController extends Controller
{
    public function __construct(
        UserChatInfoRepository $userChatInfoRepository,
        AnswerableMessageRepository $answerableMessageRepository,
        GatewaySettingsRepository $gatewaySettingsRepository
    )
    {
        $this->userChatInfoModel = $userChatInfoRepository;
        $this->answerableMessageModel = $answerableMessageRepository;
        $this->gatewaySettingsModel = $gatewaySettingsRepository;
    }

    public function setWebHook(Request $request) {
        $data = $request->all();
        $result = AppViberService::setWebHook($data['token'],$data['origin']);
        return response()->json(["result"=>$result]);
    }

    public function catchMessage(Request $request) {
        Log::debug("VIBER WEBHOOK = ",$request->all());
        $token = $request->route('token');
        $requestData = $request->all();
        switch ($requestData['event']){
            case "conversation_started":
            case "subscribed":
                $gatewaySetting = $this->gatewaySettingsModel->getProjectIdByChannelConfig('token',$request->route('token'));
                $bot = AppViberService::getAccountInfo($token,true);
                foreach ($gatewaySetting as $gate){
                    $data = [
                        'project_id'=>$gate->project_id,
                        'chat_id'=>$requestData['user']['id'],
                        'bot_id'=>$token,
                        'bot'=>json_encode($bot,JSON_UNESCAPED_UNICODE),
                        'user'=>json_encode($requestData['user'],JSON_UNESCAPED_UNICODE),
                        'channel'=>AnswerableMessage::CHANNEL_VIBER,
                    ];
                    $this->userChatInfoModel->createNewViberUserInfo($data);

                    $subscibe = $gate->subscribe;
                    if($subscibe && $subscibe->action === GatewaySubscribe::ACTION_TEMPLATE){

                        $model = [
                            "message_id"=>$requestData['message_token'],
                            "template_id"=>$subscibe->template_id,
                            "receiver"=>$requestData['user']['id'],
                            "token"=>$token
                        ];
                        $this->answerableMessageModel->createViberAnswerableQuestion($model);

                        if($requestData['event'] === 'conversation_started'){
                            $template = Template::find($subscibe->template_id);
                            $data =  AppReplyMessageService::prepareTemplateWithMenu($template);
                            $name = AppViberService::getAccountName($token);

                            $response = [
                                'auth_token'=>$token,
                                'type'=>'text',
                                'text'=>$data['text'],
                                'sender'=>[
                                    'name'=>$name
                                ]
                            ];

                            Log::debug("VIBER response on event 'conversation_started' = ",$response);
                            return response()->json($response);
                        }
                    }
                }
                break;
            case "message":
                if($requestData['message']['type'] === "text"){
                    $replyMessage = AppReplyMessageService::usersViberAnswer($requestData,$token);
                    if(!$replyMessage){
                        $model = [
                            "answer"=>$requestData['message']['text'],
                            "chat_id"=>$requestData['sender']['id'],
                            "chat_user_id"=>$requestData['sender']['id'],
                            "bot_username"=>$token
                        ];
                        $this->answerableMessageModel->createSimpleMessage($model,AnswerableMessage::CHANNEL_VIBER);
                        return;
                    }

                    $response = AppViberService::sendToMember($token,$requestData['sender']['id'],$replyMessage['text']);
                    if($response['status'] === 0 && $response['status_message'] === 'ok'){
                        if(isset($replyMessage['shouldSave']) && $replyMessage['shouldSave'] === true){
                            $model = [
                                "message_id"=>$response['message_token'],
                                "template_id"=>$replyMessage['template_id'],
                                "receiver"=>$requestData['sender']['id'],
                                "token"=>$token
                            ];
                            $this->answerableMessageModel->createViberAnswerableQuestion($model);
                        }
                        else{
                            $botSimple = [
                                "answer"=>$replyMessage['text'],
                                "chat_id"=>$requestData['sender']['id'],
                                "chat_user_id"=>$requestData['sender']['id'],
                                "bot_username"=>$token
                            ];
                            $this->answerableMessageModel->createSimpleMessage($botSimple,AnswerableMessage::CHANNEL_VIBER, AnswerableMessage::BOT_SIMPLE);
                        }
                    }
                }
                if(in_array($requestData['message']['type'],['file','picture'])){
                    $mime = AppViberService::getFileMimeTypeByUrl($requestData['message']['media']);
                    $fileJson = [
                        "file_path"=>$requestData['message']['media'],
                        "file_name"=>isset($requestData['message']['file_name']) ? $requestData['message']['file_name'] : "" ,
                        "file_size"=>isset($requestData['message']['size']) ? $requestData['message']['size'] : ""
                    ];
                    $messageData = [
                        "file_json"=>json_encode(array_merge($fileJson,$mime),JSON_UNESCAPED_UNICODE),
                        "file_path"=>$requestData['message']['media'],
                        "bot_username"=>$token,
                        "channel"=>AnswerableMessage::CHANNEL_VIBER,
                        "message_id"=>$requestData['message_token'],
                        "chat_id"=>$requestData['sender']['id'],
                        "state"=>AnswerableMessage::USER_SIMPLE,
                        "type"=>AnswerableMessage::TYPE_FILE
                    ];
                    AnswerableMessage::create($messageData);
                }
                break;
        }
    }
}