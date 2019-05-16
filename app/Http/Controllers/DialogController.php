<?php

namespace App\Http\Controllers;

use App\Http\Requests\DialogRequest\DialogUploadFilesRequest;
use App\Models\AnswerableMessage;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\UserChatInfoRepository;
use Illuminate\Http\Request;
use AppTelegramService;
use AppViberService;
use AppFacebookService;
use Illuminate\Support\Facades\Storage;


class DialogController extends Controller
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

    public function getUsers(Request $request){
        $filter = $request->only(['channel','project','search','not_seen']);
        $users  = $this->userChatInfoModel->getAll($filter,false);
        return response()->json([
            'Users'=>$users,
            'success'=>true
        ],200);
    }

    public function getMessages(Request $request){
        $data = $request->all();
        $messages = [];
        if(isset($data['channel'])){
            switch ($data['channel']){
                case "telegram":
                case "viber":
                case "fb":
                    $messages = AppTelegramService::dialogHistory($data);
                    break;
                case "whatsapp":
                    break;
            }
        }
        return response()->json([
            'DialogMessages'=>$messages,
            'success'=>true
        ],200);
    }

    public function send(Request $request){
        $data = $request->all();
        $dialogMessage = [];
        if(isset($data['channel'])){
            switch ($data['channel']){
                case AnswerableMessage::CHANNEL_TELEGRAM:
                    $gtSettings = $this->gatewaySettingsModel->getTokenByUsernameAndId($data['bot']['username'],$data['bot']['id']);
                    AppTelegramService::setToken($gtSettings['field_value']);
                    $result = AppTelegramService::replyMessage($data['chat_id'],["text"=>$data['message']]);
                    $messageData = [
                        "chat_id"=>$result['chat']['id'],
                        "answer"=>$result['text'],
                        "bot_username"=>$result['from']['username']
                    ];
                    $dialogMessage = $this->answerableMessageModel->createSimpleMessage($messageData,$data['channel'],AnswerableMessage::BOT_SIMPLE);
                    break;
                case AnswerableMessage::CHANNEL_VIBER:
                    $result = AppViberService::sendToMember($data['bot_id'],$data['chat_id'],$data['message']);
                    if($result['status'] === 0 &&  $result['status_message'] === "ok"){
                        $messageData = [
                            "chat_id"=>$data['chat_id'],
                            "answer"=>$data['message'],
                            "bot_username"=>$data['bot_id']
                        ];
                        $dialogMessage = $this->answerableMessageModel->createSimpleMessage($messageData,$data['channel'],AnswerableMessage::BOT_SIMPLE);
                    }
                    break;
                case AnswerableMessage::CHANNEL_FB:
                    $gtSettings = $this->gatewaySettingsModel->getFbSettingsByAppId($data['bot_id']);
                    $result = AppFacebookService::sendToMember($gtSettings->field_value,$data['chat_id'],$data['message']);
                    if(isset($result['message_id']) && !empty($result['message_id'])){
                        $messageData = [
                            "chat_id"=>$data['chat_id'],
                            "answer"=>$data['message'],
                            "bot_username"=>$data['bot_id'],
                            "message_id"=>$result['message_id']
                        ];
                        $dialogMessage = $this->answerableMessageModel->createSimpleMessage($messageData,$data['channel'],AnswerableMessage::BOT_SIMPLE);
                    }
                    break;
            }
        }
        return response()->json([
            'DialogMessage'=>$dialogMessage,
            'success'=>true
        ],200);
    }

    public function uploadFile(DialogUploadFilesRequest $request){
        $data = $request->all();
        try{
            $timestamp = (float)microtime() * 1000000;
            $filename = $timestamp.'_'.$data['file']->getClientOriginalName();

            $storageFilePath = 'files/dialog/'.$filename;

            $storageResult = Storage::disk('public')->put($storageFilePath, file_get_contents($data['file']),'public');
            if(!$storageResult){
                throw new \Exception("Не удалось загрузить файл ".$data['file']->getClientOriginalName());
            }

            return response()->json([
                "file_path"=>$filename,
                'success'=>true
            ]);
        }catch (\Exception $e){
            return response()->json([
                "error"=>$e->getMessage(),
                'success'=>false
            ],400);
        }
    }

    public function sendFile(Request $request){
        $data = $request->all();
        $answerableMessage = [];
        if(isset($data['channel'])){
            switch ($data['channel']){
                case AnswerableMessage::CHANNEL_TELEGRAM:
                    $gtSettingsToken = $this->gatewaySettingsModel->getTokenByUsernameAndId($data['username'],$data['bot_id']);
                    AppTelegramService::setToken($gtSettingsToken['field_value']);
                    $fileResponse = AppTelegramService::sendFile($data['chat_id'],$data['file']);
                    $messageType = AppTelegramService::getMessageType($fileResponse);
                    if($fileResponse){
                        $filePath =  AppTelegramService::getFilePath($fileResponse[$messageType]->getFileId());
                        $messageData = [
                            "file_json"=>json_encode($fileResponse[$messageType]),
                            "file_path"=>$filePath,
                            "bot_username"=>$fileResponse['from']['username'],
                            "channel"=>AnswerableMessage::CHANNEL_TELEGRAM,
                            "message_id"=>$fileResponse['message_id'],
                            "chat_id"=>$data['chat_id'],
                            "state"=>AnswerableMessage::BOT_SIMPLE,
                            "type"=>AnswerableMessage::TYPE_FILE
                        ];
                        $answerableMessage = AnswerableMessage::create($messageData);
                    }
                    break;
                case AnswerableMessage::CHANNEL_VIBER:
                    $result = AppViberService::sendFile($data['bot_id'],$data['chat_id'],$data['file']);
                    $fileJson = [];
                    if($result['status'] === 0 && $result['status_message'] === "ok"){
                        $fileJson = AppViberService::prepareFileJsonData($data['file']);
                    }
                    $messageData = [
                        "file_json"=>json_encode($fileJson),
                        "file_path"=>$fileJson['file_path'],
                        "bot_username"=>$data['bot_id'],
                        "channel"=>AnswerableMessage::CHANNEL_VIBER,
                        "message_id"=>$result['message_token'],
                        "chat_id"=>$data['chat_id'],
                        "state"=>AnswerableMessage::BOT_SIMPLE,
                        "type"=>AnswerableMessage::TYPE_FILE
                    ];
                    $answerableMessage = AnswerableMessage::create($messageData);
                    break;
                case AnswerableMessage::CHANNEL_FB:
                    $gtSettings = $this->gatewaySettingsModel->getFbSettingsByAppId($data['bot_id']);
                    $result = AppFacebookService::sendFile($gtSettings->field_value,$data['chat_id'],$data['file']);
                    if(isset($result['message_id']) && !empty($result['message_id'])) {
                        $fileJson = AppFacebookService::prepareFileJsonData($data['file']);
                        $fileJson['attachment_id'] = $result['attachment_id'];
                        $messageData = [
                            "file_json"=>json_encode($fileJson),
                            "file_path"=>$fileJson['file_path'],
                            "chat_id" => $data['chat_id'],
                            "bot_username" => $data['bot_id'],
                            "message_id" => $result['message_id'],
                            "channel"=>AnswerableMessage::CHANNEL_FB,
                            "state"=>AnswerableMessage::BOT_SIMPLE,
                            "type"=>AnswerableMessage::TYPE_FILE
                        ];
                        $answerableMessage = AnswerableMessage::create($messageData);
                    }
                    break;
            }
        }
        return response()->json(['DialogMessage'=>$answerableMessage,'success'=>true]);
    }
}