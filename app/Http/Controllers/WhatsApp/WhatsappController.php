<?php

namespace App\Http\Controllers\WhatsApp;


use App\Http\Controllers\Controller;
use App\Models\AnswerableMessage;
use App\Models\GatewaySetting;
use App\Models\MenuItem;
use App\Models\PresentReply;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use AppReplyMessageService;

class WhatsappController extends Controller
{
    public function catchMessage(Request $request){
        $answerText = $this->usersAnswer($request->all());
        $to = str_replace("whatsapp:", "",$request->get('To') );
        $settings = GatewaySetting::query()
            ->select(['gateway_settings.project_messenger_id'])
            ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
            ->where([
                'gateway_settings.field_value'=>$request->get('AccountSid'),
                'G2.field_value'=>$to
            ])->first()->toArray();
        $token = GatewaySetting::query()->select(['field_value'])->where([
            'project_messenger_id'=>$settings['project_messenger_id'],
            'field_name'=>'token'
            ])->first();
        if($answerText){
            $twilio = new Client($request->get('AccountSid'), $token['field_value']);
            $message = $twilio->messages
                ->create($request->get('From'),
                    array(
                        "body" => $answerText,
                        "from" => $request->get('To')
                    )
                );
        }
    }

    /**
     * @param $message
     * @return bool
     */
    private function usersAnswer($message){
        $chatId = $message['AccountSid'];
        $from = str_replace("whatsapp:", "",$message['From'] );
        $lastQuestion = AnswerableMessage::query()
            ->where([
                'chat_id'=>$chatId,
                'chat_user_id'=>$from,
                'channel'=>AnswerableMessage::CHANNEL_WHATSAPP
            ])
            ->orderBy('id','desc')
            ->first();
        if($lastQuestion){
            if($lastQuestion->state === AnswerableMessage::WAITING){
                $template = Template::find($lastQuestion->template_id);
                $menu = $template
                    ->menu()
                    ->select(['id','callback_url'])
                    ->with(['menuItems'])
                    ->first();
                $points = collect([]);
                if($menu){
                    $points = $menu->menuItems->map(function($val){
                        return $val->point;
                    });
                }
                if(in_array($message['Body'],$points->toArray())){
                    foreach ($menu->menuItems as $menuItem){
                        if($menuItem->point == $message['Body']){
                            if($menuItem->callback_url){
                                $this->callCallbackUrl($menuItem->callback_url);
                            }elseif ($menu->callback_url){
                                $this->callCallbackUrl($menu->callback_url);
                            }
//                            $this->updateAnswerableMessage($lastQuestion,$message['Body'],AnswerableMessage::ANSWERED);
                            AppReplyMessageService::saveUsersAnswer($lastQuestion,$message['Body'],AnswerableMessage::ANSWERED);
                            Log::debug('WhatsApp MESSAGE ANSWERED',[$menuItem]);
                            if($menuItem->auto_reply){
                                Log::debug('WhatsApp MESSAGE ANSWERED');
                                return $this->autoReply($menuItem);
                            }else{
                                return false;
                            }
                            //return true;
                        }
                    }
                }
            }else{
                Log::info("The last message has been answered, MessageId = ".$lastQuestion->message_id." / ChatId = ".$lastQuestion->chat_id);
                return false;
            }
        }else{
            Log::info("The message not found, MessageId = ".$lastQuestion->message_id." / ChatId = ".$lastQuestion->chat_id);
            return false;
        }
        return false;
    }


    /**
     * @param $url
     */
    private function callCallbackUrl($url){
        Log::debug("callCallbackUrl CALLED");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10
        ]);
        $response = curl_exec($curl);
        try{
            Log::debug("callCallbackUrl:response(success) ",[$response]);
        }
        catch (\Exception $e){
            Log::debug("callCallbackUrl:log:error ",[$e->getMessage()]);
        }
    }

    /**
     * @param AnswerableMessage $lastQuestion
     * @param $answer
     * @param $state
     */
    private function updateAnswerableMessage(AnswerableMessage $lastQuestion,$answer,$state){
        $lastQuestion->setAttribute('answer',$answer);
        $lastQuestion->setAttribute('state',$state);
        $lastQuestion->save();
    }

    /**
     * @param MenuItem $menuItem
     * @return bool
     */
    private function autoReply(MenuItem $menuItem){
        if($menuItem->reply_type === MenuItem::REPLY_PRESENT){
            $presentReply = PresentReply::inRandomOrder()->where(['point'=>$menuItem->point,'menu_id'=>$menuItem->menu_id])->first();
            if($presentReply){
                Log::info('PRESENT REPLY',[$presentReply->text]);
                return $presentReply->text;
            }else{
                return false;
            }
        }elseif ($menuItem->reply_type === MenuItem::REPLY_TEMPLATE){
            if($menuItem->template_id){
                $template = Template::find($menuItem->template_id);
                if($template){
                    return $template->text;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
