<?php

namespace App\Services;

use App\Repositories\AnswerableMessageRepository;
use function foo\func;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram;
use AppReplyMessageService;

/**
 * Class AppTelegram
 * @package App\Http\Services
 */
class AppTelegram
{
    private $token = null;
    public function __construct(
        AnswerableMessageRepository $answerableMessageRepository
    )
    {
        $this->answerableMessageModel = $answerableMessageRepository;
        if(request()->route('token')){
            $this->setToken(request()->route('token'));
        }
    }

    /**
     * @param $token
     * @param null $url
     * @return bool
     */
   public function setWebHook($token,$origin = null)
    {
        try {
            $this->setToken($token);
            if($origin === null){
                $url = "https://" . request()->getHttpHost() . "/api/telegram/" . $token;
            }else{
                $url = $origin."/api/telegram/" . $token;
            }
            $result = Telegram::setWebhook([
                "url" => $url,
                'content-type' => 'application/json'
            ]);
            Log::info("SET WEB HOOK",[$result]);
            return $result;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * @param null $token
     * @return bool
     */
    public function setToken($token = null){
        if( $token !== null){
            $this->token = $token;
            Config::set('telegram.bot_token',$token);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isBotCommand(){
        if(isset(Telegram::getWebhookUpdates()['message']['text'])){
            $commands = array_keys(Telegram::getCommands());
            $command = str_replace('/', "", Telegram::getWebhookUpdates()['message']['text']);
            return in_array($command,$commands);
        }
        return false;
    }

    public function commandHandler(){
        Telegram::commandsHandler(true);
    }

    public function replyMessage($chat_id,$data) {
        $replyData = ['chat_id'=>$chat_id];
        if(isset($data['keyboard']) && count($data['keyboard'])){
            $replyData["reply_markup"] = Telegram::replyKeyboardMarkup([
                "keyboard"=>$data['keyboard'],
                "resize_keyboard"=>true,
                "one_time_keyboard"=>true
            ]);

        }else{
            $replyData["reply_markup"] = Telegram::replyKeyboardHide();
        }
        $replyData['text'] = $data['text'];
        try{
            return  Telegram::sendMessage($replyData);
        }catch (\Exception $e){
            return false;
        }
    }

    public function getBotUsername(){
        $user = Telegram::getMe();
        return $user->getUsername();
    }

    public function dialogHistory($data){
        try{
            $errorMessage = [];
            if(!isset($data['bot_username']) || empty($data['bot_username'])){
                $errorMessage['username'] = 'The username is empty';
            }
            if(!isset($data['chat_id']) || empty($data['chat_id'])){
                $errorMessage['chat_id'] = 'The chat_id is empty';
            }
            if(count($errorMessage)){
                return [
                    "data"=>[],
                    "error"=>$errorMessage
                ];
            }
            $history = $this->answerableMessageModel->getAll($data["channel"],$data);

            $ides = $history->map(function($val){
                return $val->id;
                });
            $this->answerableMessageModel->updateSeen($ides);
            return $history;

        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public function sendFile($chat_id,$file){
//        $t = Storage::disk('public')->put('files/telegram/', $file,'public');
        //dd(Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix()."files/telegram".$file);
        $data = [
            'chat_id'=>$chat_id,
            'document'=>Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix()."files/dialog/".$file
            ];

        try{
            $result = Telegram::sendDocument($data);
            //unlink(Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix()."/files/telegram/".$file);
            return $result;
        }catch (\Exception $e){
            return false;
        }
    }

    public function getFilePath($fileId){
        $result = Telegram::getFile(['file_id'=>$fileId]);
        if($result){
            return  "https://api.telegram.org/file/bot".$this->token ."/". $result->getFilePath();
        }
        return null;
    }

    public function getMessageType($message){
        foreach (['document','audio','video','voice','photo'] as $val){
            if(isset($message[$val])){
                return $val;
            }
        }
    }
}