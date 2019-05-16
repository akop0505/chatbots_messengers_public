<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppViber
{
    private $baseUrl = "https://chatapi.viber.com/pa";

    public function __construct()
    {
    }

    public function setWebHook($token,$origin = null){
        if($origin === null){
            $url = "https://" . request()->getHttpHost() . "/api/viber/" . $token;
        }else{
            $url = $origin."/api/viber/" . $token;
        }
        $data = [
            "auth_token"=>$token,
            "url"=>$url,
            "event_types"=>[
                "delivered",
                "seen",
                "failed",
                "subscribed",
                "unsubscribed",
                "conversation_started"
            ],
            "send_name"=> true,
            "send_photo"=> true
        ];
        $result = $this->sendRequest('set_webhook',"post",$data);
        Log::debug('AppViberService:setWebHook',[$result]);
        return $result;
    }

    /**
     * @param $token
     * @return array|mixed
     */
    public function getMembers($token){
        $data = ['auth_token'=>$token];
        $result =  $this->sendRequest('get_account_info','post',$data);
        return count($result) ? $result['members'] : [];
    }

    /**
     * @param $token
     * @return array|mixed
     */
    public function getAccountName($token){
        $data = ['auth_token'=>$token];
        $result =  $this->sendRequest('get_account_info','post',$data);
        return count($result) ? $result['name'] : [];
    }

    public function sendToMember($token,$receiver,$text){
        $name = $this->getAccountName($token);
        $data = [
            'auth_token'=>$token,
            'type'=>'text',
            'text'=>$text,
            'receiver'=>$receiver,
            'sender'=>[
                'name'=>$name
            ]
        ];
        $result =  $this->sendRequest('send_message','post',$data);
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
        $name = $this->getAccountName($token);
        $data = [
            'auth_token'=>$token,
            'type'=>'file',
            'media'=>env('APP_URL_DOMAIN','https://5m3.online')."/api/storage/".$file,
            'receiver'=>$receiver,
            'size'=>Storage::disk('public')->size("files/dialog/".$file),
            'file_name'=>$file,
            'sender'=>[
                'name'=>$name
            ]
        ];
        $result =  $this->sendRequest('send_message','post',$data);
        return $result;
    }


    /**
     * @param $token
     * @param bool $basic
     * @return array|mixed
     */
    public function getAccountInfo($token,$basic = false){
        $params = [
            'auth_token'=>$token
        ];
        $result = $this->sendRequest("get_account_info",'post',$params);
        if($result['status'] !== 0 && $result['status_message'] !== "ok"){
            return [];
        }
        if($basic){
            unset($result['webhook']);
            unset($result['event_types']);
            unset($result['members']);
            unset($result['chat_flags']);
            unset($result['subscribers_count']);
        }
        return $result;
    }

    /**
     * @param $method
     * @param string $type
     * @param array $data
     * @return array|mixed
     */
    public function sendRequest($method,$type = "get",$data = []) {
        $client = new Client();
        $url = $this->baseUrl."/".$method;

        if($type === 'post'){
            $headers = [
                'content-type' => 'application/json'
            ];
            $data_json = json_encode($data);
            $result = $client->post($url,[
                "headers"=>$headers,
                "body"=>$data_json
            ]);
        }else{
            $result = $client->get($url);
        }
        if($result->getStatusCode() === 200){
            return json_decode($result->getBody()->getContents(),true);
        }else{
            Log::error("AppViber:sendRequest",[$result]);
            return [];
        }
    }

    public function getFileMimeTypeByUrl($url) {
        $headers = get_headers($url);
        $contentType = null;
        foreach ($headers as $header){
            if(strpos($header, "Content-Type")!== false){
                $contentType = $header;
                break;
            }
        }
        if($contentType){
            $type = explode(": ",$contentType);
            return ['mime_type'=>$type[1]];
        }
        return ['mime_type'=>""];
    }
}