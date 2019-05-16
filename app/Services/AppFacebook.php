<?php

namespace App\Services;

use AppFacebookService;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\UserChatInfoRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class AppFacebook
{
    private $baseUrl = "https://graph.facebook.com/v3.2/me";

    public function __construct(
        UserChatInfoRepository $userChatInfoRepository,
        GatewaySettingsRepository $gatewaySettingsRepository
    )
    {
        $this->userChatInfoModel = $userChatInfoRepository;
        $this->gatewaySettingsModel = $gatewaySettingsRepository;
    }

    public function getMessaging($message){
        if($message['entry']){
            foreach ($message['entry'] as $key=>$entry){
                if(key_exists('messaging',$entry)){
                   if (key_exists('message',$entry['messaging'][0])){
                       $info = $entry['messaging'][0];
                       $info['id'] = $entry['id'];
                       $info['time'] = $entry['time'];
                       if(isset($info['message']['is_echo']) && $info['message']['is_echo'] === true){
                           $info['is_bot'] = true;
                       }
                       else{
                           $info['is_bot'] = false;
                       }
                       return $info;
                   }
                }
            }
        }
        return false;
    }

    public function subscribe($appId,$message){

        $gatewaySettingProjects = $this->gatewaySettingsModel->getProjectIdByChannelConfig('app_id',$appId);
        $gatewaySettingToken = $this->gatewaySettingsModel->getFbSettingsByAppId($appId);
        $botInfo = AppFacebookService::getUserInfo($gatewaySettingToken->field_value,$appId);
        foreach ($gatewaySettingProjects as $gate){
            $data = [
                "token"=>$gatewaySettingToken->field_value,
                "project_id"=>$gate['project_id'],
                "chat_id"=>$message['sender']['id'],
                "bot"=>$botInfo,
                "bot_id"=>$appId,
            ];
            $this->userChatInfoModel->createNewFbUserInfo($data);
        }
    }

    public function sendToMember($token,$receiver,$text){
        $data = [
            'message'=>[
                'text'=>$text
            ],
            'recipient'=>[
                'id'=>$receiver
            ],
            "access_token"=>$token
        ];
        $result =  $this->sendRequest('messages','post',$data);
        return $result;
    }

    public function prepareFileJsonData($file){
        $fileStoragePath = storage_path('/app/public/files/dialog/' . $file);
        $fileJson = [
            "file_path"=>env('APP_URL_DOMAIN','https://5m3.online')."/api/storage/".$file,
            "file_name"=>$file,
            "mime_type"=>File::mimeType($fileStoragePath),
            "file_size"=>File::size($fileStoragePath)
        ];
        return $fileJson;
    }

    public function sendFile($token,$receiver,$file){
        $data = [
            [
                'name'=>'access_token',
                'contents'=>$token
            ],
            [
                'name'=>'recipient[id]',
                'contents'=>$receiver
            ],
            [
                'name'=>'message[attachment][type]',
                'contents'=>'file'
            ],
            [
                'name'=>'message[attachment][payload][is_reusable]',
                'contents'=>true
            ],
            [
                'name'=>'filedata',
                'contents'=>fopen(Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix()."files/dialog/".$file,'r')
            ]
        ];
        $result =  $this->sendRequest('messages','post',$data,true);
        return $result;
    }

    public function getUserInfo($token,$userId,$fields = false){
        $this->baseUrl = "https://graph.facebook.com";
        $params = [
            'access_token'=>$token
        ];
        if($fields === true){
            $params['fields'] = 'first_name,last_name,profile_pic,locale,timezone,gender';
        }
        return $this->sendRequest($userId,'get',$params);
    }

    /**
     * @param $method
     * @param string $type
     * @param array $data
     * @param boolean $file
     * @return array|mixed
     */
    public function sendRequest($method,$type = "get",$data = [], $file = false) {
        $client = new Client();
        $url = $this->baseUrl."/".$method;

        if($type === 'post'){
            $headers = [
                'content-type' => 'application/json',
            ];
            $requestData = [];
            if($file === true){
                $requestData['multipart'] = $data;
            }else{
                $data_json = json_encode($data);
                $requestData["headers"] = $headers;
                $requestData["body"] = $data_json;
            }
            $result = $client->post($url,$requestData);
        }else{
            $result = $client->get($url,["query"=>$data]);
        }
        if($result->getStatusCode() === 200){
            return json_decode($result->getBody()->getContents(),true);
        }else{
            Log::error("AppViber:sendRequest",[$result]);
            return [];
        }
    }
}