<?php

namespace App\Jobs;

use App\Models\AnswerableMessage;
use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;


class WhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $phones = [];
    private $text = "";
    private $sid = null;
    private $token = null;
    private $from = null;
    private $template = null;
    private $menu = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phones,$text,$configs, Template $template)
    {
        $this->phones  = $phones;
        $this->text = $text;
        $this->sid =   $configs['sid'] ? : null;
        $this->token = $configs['token'] ? : null;
        $this->from = $configs['phone'] ? : null;
        $this->menu = null;
        $this->template = $template;
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
        $twilio = new Client($this->sid, $this->token);
        $menuText = "";
        try{
            $resultArray = [];
            $needSave = false;

            if($this->menu){
                $needSave = true;
                $menuText = $this->menu->name;

                foreach ($this->menu->menuItems as $k=>$item){
                    $menuText .= "\n";
                    $menuText .= $item->point . " - " . $item->name;
                }
            }
            foreach ($this->phones as $phone){
                $message = $twilio->messages
                    ->create("whatsapp:".$phone,
                        array(
                            "body" => $menuText ? $this->text."\n\n".$menuText : $this->text,
                            "from" => "whatsapp:".$this->from
                        )
                    );
                array_push($resultArray,$message);
                if($needSave){
                    $this->saveMessage($message,$phone);
                }
                Log::info("Messaage RESULT = ",[$message]);
                Log::info("Messaage SID = ",[$message->sid]);
            }
            Log::info("WhatsApp: send message result = ",$resultArray);
        }catch (\Exception $exception){
            Log::error("WhatsApp: send message ERROR = ",[$exception->getMessage()]);
        }
    }

    private function saveMessage($message,$phone){
        $model = new AnswerableMessage();
        $model->fill([
            "message_id"=>$message->sid,
            "template_id"=>$this->template->id,
            "chat_id"=>$message->accountSid,
            "chat_user_id"=>$phone,
            "state"=>AnswerableMessage::WAITING,
            "channel"=>AnswerableMessage::CHANNEL_WHATSAPP,
        ]);
        $saveResut = $model->save();
        Log::debug("WHATSAPP SAVE IN DB RESULT",[$saveResut]);
    }
}
