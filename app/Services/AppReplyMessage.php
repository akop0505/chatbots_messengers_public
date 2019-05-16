<?php

namespace App\Services;

use App\Models\AnswerableMessage;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\PresentReply;
use App\Models\Template;
use App\Repositories\AnswerableMessageRepository;
use App\Repositories\GatewaySettingsRepository;
use App\Repositories\PresentReplyRepository;
use App\Repositories\TemplateRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use DB;

/**
 * Class AppReplyMessage
 * @package App\Services
 */
class AppReplyMessage
{

    public function __construct(
        GatewaySettingsRepository $gatewaySettingsRepository,
        AnswerableMessageRepository $answerableMessageRepository,
        TemplateRepository $templateRepository,
        PresentReplyRepository $presentReplyRepository
    )
    {
        $this->gatewaySettingsModel = $gatewaySettingsRepository;
        $this->answerableMessageModel = $answerableMessageRepository;
        $this->templateModel = $templateRepository;
        $this->presentReplyModel = $presentReplyRepository;
    }

    /**
     * @param $message
     * @param $token
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function usersTelegramAnswer($message,$token){
        $chatId = $message['chat']['id'];
        $settings = $this->gatewaySettingsModel->getUsernameByToken($token)->toArray();

        $lastQuestion = $this->answerableMessageModel->getTelegramsLastQuestion($chatId,$settings['field_value']);
        if($lastQuestion){
            return $this->replyToWaitingMessage($lastQuestion,$message);
        }else{
            Log::info("The message not found");
            return false;
        }
    }

    /**
     * @param $message
     * @param $token
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function usersViberAnswer($message,$token){
        $chatId = $message['sender']['id'];

        $lastQuestion = $this->answerableMessageModel->getViberLastQuestion($chatId,$token);
        $messagesNewForm = [];
        if($lastQuestion){
            $messagesNewForm['text'] = $message['message']['text'];
            return $this->replyToWaitingMessage($lastQuestion,$messagesNewForm);
        }else{
            Log::info("The message not found");
            return false;
        }
    }

    public function usersFbAnswer($message,$appId){
        $lastQuestion = $this->answerableMessageModel->getFbLastQuestion($message['sender']['id'],$appId);
        $messagesNewForm = [];
        if($lastQuestion){
            $messagesNewForm['text'] = $message['message']['text'];
            return $this->replyToWaitingMessage($lastQuestion,$messagesNewForm);
        }else{
            Log::info("The message not found");
            return false;
        }
    }

    /**
     * @param AnswerableMessage $lastQuestion
     * @param $message
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function replyToWaitingMessage(AnswerableMessage $lastQuestion,$message){
        if($lastQuestion->state === AnswerableMessage::WAITING) {
            $template = $this->templateModel->getById($lastQuestion->template_id);
            $menu = $template->getMenu();
            $points = $this->getPoints($menu);
            if(in_array($message['text'],$points->toArray())){
                return $this->findReplyMessage($lastQuestion,$message['text'],$menu);
            }else{
                $defMenuItem = $menu->menuItems()->where(['default'=>true])->first();
                if($defMenuItem){
                    return $this->findReplyMessage($lastQuestion,$defMenuItem['point'],$menu,$message['text']);
                }
            }
        }else{
            Log::info("The last message has been answered, MessageId = ".$lastQuestion->message_id." / ChatId = ".$lastQuestion->chat_id);
            return false;
        }

    }

    /**
     * @param $menu
     * @return \Illuminate\Support\Collection
     */
    private function getPoints($menu){
        $points = collect([]);
        if($menu){
            $points = $menu->menuItems->map(function($val){
                return $val->point;
            });
        }
        return $points;
    }

    /**
     * @param AnswerableMessage $lastQuestion
     * @param $point
     * @param $menu
     * @param null $text
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function findReplyMessage(AnswerableMessage $lastQuestion,$point,$menu,$text = null){
        foreach ($menu->menuItems as $menuItem){
            if($menuItem->point == $point){
                $answer = $menuItem->default === 1 ? $text : $point;
                if(!$this->saveUsersAnswer($lastQuestion,$answer,AnswerableMessage::ANSWERED)){
                    return false;
                }
                if($menuItem->auto_reply){
                    return $this->autoReply($menu,$menuItem,$lastQuestion);
                }else{
                    return false;
                }
            }
        }

    }

    private function callCallbackUrl(Menu $menu, MenuItem $menuItem){
        if($menuItem->callback_url){
            $this->sendRequest($menuItem->callback_url);
        }elseif ($menu->callback_url){
            $this->sendRequest($menu->callback_url);
        }
    }

    /**
     * @param $url
     * @param array $data
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest($url,$data = []){
        Log::debug("callCallbackUrl CALLED",[$url]);
        try{
            $client = new Client();
            $res = $client->post( $url,[
                'headers'=>['content-type'=>'application/json'],
                'body'=>json_encode($data)
            ]);
            if($res->getStatusCode() == 200){
                $responseData = json_decode($res->getBody()->getContents(),200);
                Log::debug("callCallbackUrl:response(success) ",[$responseData]);
                return $responseData;
            }else{
                Log::debug("callCallbackUrl:response(ERROR) ",[$res->getStatusCode()]);
                return false;
            }
        }
        catch (\Exception $e){
            return false;
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
     * @param AnswerableMessage $lastQuestion
     * @param $answer
     * @param $state
     * @return bool
     */
    private function saveUsersAnswer(AnswerableMessage $lastQuestion,$answer,$state){
        try{
            DB::beginTransaction();

            $lastQuestion->setAttribute('state',$state);
            $lastQuestion->save();
            $newMessage = new AnswerableMessage();
            $newMessage->fill([
                'parent_id'=>$lastQuestion->id,
                'answer'=>$answer,
                'chat_id'=>$lastQuestion->chat_id,
                'bot_username'=>$lastQuestion->bot_username,
                'channel'=>$lastQuestion->channel,
                'state'=>AnswerableMessage::USER_ANSWER
            ]);
            $newMessage->save();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * @param Menu $menu
     * @param MenuItem $menuItem
     * @param AnswerableMessage $lastQuestion
     * @return array|bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function autoReply(Menu $menu,MenuItem $menuItem,AnswerableMessage $lastQuestion) {
        if($menuItem->reply_type === MenuItem::REPLY_PRESENT){
            $this->callCallbackUrl($menu, $menuItem);
            $presentReply = $this->presentReplyModel->getRandomReplyMessage($menuItem->point,$menuItem->menu_id);
            if($presentReply){
                Log::info('PRESENT REPLY',[$presentReply->text]);
                return ["text"=>$presentReply->text];
            }else{
                return false;
            }
        }elseif ($menuItem->reply_type === MenuItem::REPLY_TEMPLATE){
            if($menuItem->template_id){
                $template = Template::find($menuItem->template_id);
                if($template){
                    return  $this->replyTemplate($template,$menu,$menuItem,$lastQuestion);
                }
            }else{
                return false;
            }
        }elseif ($menuItem->reply_type === MenuItem::REPLY_LOGIC_TEMPLATE){
            if($menuItem->template_id){
                $templates = Template::find([$menuItem->template_id, $menuItem->false_template_id]);
                if($templates){
                    $templates = [
                        'true' => $templates->firstWhere('id', $menuItem->template_id),
                        'false' => $templates->firstWhere('id', $menuItem->false_template_id),
                    ];
                    return $this->replyTemplate($templates,$menu,$menuItem,$lastQuestion);
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param mixed $template
     * @param Menu $menu
     * @param MenuItem $menuItem
     * @param AnswerableMessage $lastQuestion
     * @return array ['text'=>string , 'shouldSave'=>boolean, 'keyboard'=> Array]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function replyTemplate($template,Menu $menu,MenuItem $menuItem,AnswerableMessage $lastQuestion){
        if( $template instanceof Template) {
            $data = [
                "project_id" => $template->project_id,
                "menu" => ["id" => $menu->id, "name" => $menu->name],
                "answer_code" => $menuItem->point,
                "channel" => $lastQuestion->channel,
                "template" => ["id" => $template->id, "name" => $template->name],
                "need_variables" => $this->getTemplatesVariable($template->text),
                "user_id" => $lastQuestion->chat_id,
                "message_parameters" => $lastQuestion->message_params,
            ];
            $response = $this->sendRequest($menuItem->callback_url, $data);
            if ($response && count($response['variables'])) {
                return $this->prepareTemplateWithMenu($template, $response['variables']);
            } else {
                return $this->prepareTemplateWithMenu($template);
            }
        }else if(is_array($template)){
            $data = [
                "logical" => true,
                "project_id" => $template['true']->project_id,
                "menu" => ["id" => $menu->id, "name" => $menu->name],
                "answer_code" => $menuItem->point,
                "answer" => $lastQuestion->userAnswer->answer,
                "channel" => $lastQuestion->channel,
                "template" => [
                    'true' => ["id" => $template['true']->id, "name" => $template['true']->name],
                    'false' => ["id" => $template['false']->id, "name" => $template['false']->name],
                ],
                "need_variables" => [
                    'true' => $this->getTemplatesVariable($template['true']->text),
                    'false' => $this->getTemplatesVariable($template['false']->text),
                ],
                "user_id" => $lastQuestion->chat_id,
                "message_parameters" => $lastQuestion->message_params,
            ];
            $response = $this->sendRequest($menuItem->callback_url, $data);
            $logic = $response['logic_result'] ? 'true' : 'false';
            if ($response && count($response['variables'])) {
                return $this->prepareTemplateWithMenu($template[$logic], $response['variables']);
            } else {
                return $this->prepareTemplateWithMenu($template[$logic]);
            }
        }
        return false;
    }
    private function getTemplatesVariable($text){
        $matches = [];
        $variables = [];
        preg_match_all('/{\w*}/', $text, $matches);
        if(count($matches[0])){
            foreach ($matches[0] as $match){
                $variable = str_replace(["{","}"],"",$match);
                array_push($variables,$variable);
            }
        }
        return $variables;
    }

    /**
     * @param Template $template
     * @param null $variables
     * @return array ['text'=>string , 'shouldSave'=>boolean, 'keyboard'=> Array]
     */
    public function prepareTemplateWithMenu(Template $template,$variables = null){
        $templateText = $variables !==null ? $this->prepareTemplateText($template->text,$variables) : $template->text;
        $messageData = [
            'template_params'=>$variables !==null ? $variables : [],
            'shouldSave' => false
        ];
        $menuText = "";
        if($template->menu) {
            $menuText = $template->menu->name;
            $pointCount = count($template->menu->menuItems->toArray());
            $j = 0;
            foreach ($template->menu->menuItems as $k=>$item){
                $menuText .= "\n";
                $menuText .= $item->point . " - " . $item->name;
                if($k%(integer)sqrt($pointCount) === 0 ){
                    $j++;
                    $keyboard[$j-1] = [];
                }
                array_push($keyboard[$j-1],$item->point);
            }
            $messageData['keyboard'] = $keyboard;
            $messageData['shouldSave'] = true;
            $messageData['template_id'] = $template->id;
        }
        $messageData['text'] = $menuText ? $templateText."\n\n".$menuText : $templateText;
        return $messageData;
    }

    private function prepareTemplateText($text,$variables){
        foreach ($variables as $key=>$val){
            $text = str_replace("{".$key."}",$val,$text);
        }
        return  $text;
    }

    /**
     * @param $message
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function usersWhatsappAnswer($message){
        $chatId = $message['AccountSid'];
        $from = str_replace("whatsapp:", "",$message['From'] );
        $lastQuestion = $this->answerableMessageModel->getWhatsappLastQuestion($chatId,$from);

        if($lastQuestion){
            return $this->replyToWaitingMessage($lastQuestion,$message);
        }else{
            Log::info("The message not found");
            return false;
        }
    }
}