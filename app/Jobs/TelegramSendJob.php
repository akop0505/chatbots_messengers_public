<?php

namespace App\Jobs;

use App\Models\AnswerableMessage;
use App\Models\ProjectMessenger;
use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;

class TelegramSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $token = null;
    private $chat = null;
    private $text = null;
    private $menu = null;
    private $template = null;
    private $template_params = null;
    private $projectMessengerId = null;
    private $message_params = null;

    /**
     * TelegramSendJob constructor.
     * @param $token
     * @param $chat
     * @param $text
     * @param Template $template
     */
    public function __construct($settings,Template $template,$data)
    {
        $this->token = $settings['token'] ?:null;
        $this->chat = $settings['chat'] ?:null;
        $this->text = $settings['text'] ?:null;
        $this->projectMessengerId = $settings['projectMessengerId'] ?:null;
        $this->template = $template;
        $this->template_params = $data['parameters'];
        $this->message_params = $data;
        if($template->menu){
            $this->menu = $template->menu()->with('menuItems')->first();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $telegram = new Api($this->token);
        $keyboard = [];
        $menuText = "";

        $messageData = [
            "chat_id"=>$this->chat,
            "reply_markup" => $telegram->replyKeyboardHide(),
            "text"=>$menuText ? $this->text."\n\n".$menuText : $this->text,
        ];
        $needSave = false;

        if($this->menu){
            $needSave = true;
            $menuText = $this->menu->name;
            $pointCount = count($this->menu->menuItems->toArray());
            $j = 0;
            foreach ($this->menu->menuItems as $k=>$item){
                $menuText .= "\n";
                $menuText .= $item->point . " - " . $item->name;
                if($k%(integer)sqrt($pointCount) === 0 ){
                    $j++;
                    $keyboard[$j-1] = [];
                }
                array_push($keyboard[$j-1],$item->point);
            }
            $reply_markup = $telegram->replyKeyboardMarkup([
                "keyboard"=>$keyboard,
                "resize_keyboard"=>true,
                "one_time_keyboard"=>true
            ]);

            $messageData['text'] = $menuText ? $this->text."\n\n".$menuText : $this->text;
            $messageData['reply_markup'] = $reply_markup;
        }
        $telegram->setAsyncRequest(false);
        try{
            $reponse = $telegram->sendMessage($messageData);
            if($needSave){
                $this->saveMessage($reponse->getMessageId());
            }
        }
        catch (TelegramResponseException $exception){
            Log::error("Telegram: Send message TelegramResponseException: ".$exception->getMessage());
        }
        catch (\Exception $e){
            Log::error("Telegram: Send message Exception: ".$e->getMessage());
        }
    }

    private function saveMessage($messageId) {
        $projectMessenger = ProjectMessenger::find($this->projectMessengerId);
        $gatewaySettings = $projectMessenger->getGatewaySettings();
        Log::debug('BOT USERNAME',$gatewaySettings->toArray());
        Log::debug('TELEGRAM TELEGRAM PARAMS',$this->template_params);
        $model = new AnswerableMessage();
        $model->fill([
            "message_id"=>$messageId,
            "message_params"=>$this->message_params,
            "template_id"=>$this->template->id,
            "template_params"=>json_encode($this->template_params),
            "chat_id"=>$this->chat,
            "chat_user_id"=>$this->chat,
            "state"=>AnswerableMessage::WAITING,
            "channel"=>AnswerableMessage::CHANNEL_TELEGRAM,
            "bot_username"=>$gatewaySettings['username']
        ]);
        $model->save();
    }
}
