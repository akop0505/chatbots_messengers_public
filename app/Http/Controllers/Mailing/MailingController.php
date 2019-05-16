<?php

namespace App\Http\Controllers\Mailing;

use App\Http\Controllers\Controller;
use App\Http\Helpers\SendPulse\EmailSendPulse;
use App\Http\Requests\MailingRequest\SendRequest;
use App\Http\Services\MangoOffice\MangoOffice;
use App\Jobs\EmailSendPulseJob;
use App\Jobs\FacebookSendJob;
use App\Jobs\SendSMSJob;
use App\Jobs\SendTwilioSMSJob;
use App\Jobs\TelegramSendJob;
use App\Jobs\ViberSendJob;
use App\Jobs\WhatsAppJob;
use App\Models\ProjectMessenger;
use App\Models\Template;
use App\Models\UsersChatInfo;
use App\Repositories\UserChatInfoRepository;
use Telegram\Bot\Api;
use Telegram;
use AppViberService;
use AppFacebookService;


class MailingController extends Controller
{

    public function __construct(
        UserChatInfoRepository $userChatInfoRepository
    )
    {
        $this->userChatInfoModel = $userChatInfoRepository;
        $this->responseStatus = false;
        $this->responseCode = 400;
        $this->responseMessage = "";
    }

    /**
     * @SWG\Post(
     *   path="/send",
     *   tags={"ОЧЕРЕДЬ СООБЩЕНИЙ"},
     *   summary="Довавить сообшения в очередь",
     *   consumes={"application/json;charset=UTF-8"},
     *   produces={"application/json, text/plain"},
     *   security={
     *     {"Bearer": {}},
     *   },
     *      @SWG\Parameter(
     *         name="project_id",
     *         in="formData",
     *         type="string",
     *         description="Ключь доступа",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="channel",
     *         in="formData",
     *         type="string",
     *         enum={"sms", "email", "viber", "whatsapp", "telegram","fb"},
     *         description="Канал",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="phones",
     *         in="formData",
     *         type="array",
     *         @SWG\Items(type="string"),
     *         description="Номера телефонов, если канал - sms или whatsapp",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="from",
     *         in="body",
     *         type="object",
     *          @SWG\Schema(
     *          type="object",
     *          ),
     *          description="Адрес электронной почты отправителя, в виде { name:`senderName`, email:`senderaemail@test.com` }, если канал - email",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="to",
     *         in="body",
     *     type="string",
     *     format="json",
     *     @SWG\Schema(
     *          type="string",
     *          ),
     *         description="Массив объектов содержащих электронные адреса получателей, в виде [ { email:`receiveraddress@test.com` } ], если какнал - email",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="template_id",
     *         in="formData",
     *         type="integer",
     *         description="Шаблон (id)",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="event_id",
     *         in="formData",
     *         type="integer",
     *         description="Событие (id)",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="receiver_id",
     *         in="formData",
     *         type="integer",
     *         description="Получатель (id)",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="country",
     *         in="formData",
     *         type="string",
     *         enum={"en", "ru"},
     *         description="Язык (lng code)",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="parameters",
     *         in="body",
     *         @SWG\Schema(
     *          type="object",
     *     ),
     *         description="Объект переменных шаблона в виде {client_name: сергей, ...}",
     *         required=true,
     *     ),
     *
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function send(SendRequest $request){
        $data = $request->all();
        $template = Template::query()
            ->where([
                'country'=>$data['country'],
                'id'=>$data['template_id'],
                'event_id'=>$data['event_id'],
                'receiver_id'=>$data['receiver_id']
                ])
            ->whereIn('project_id',function($query) use($data){
                $query->from('project_keys')
                    ->select('project_id')
                    ->where(['access_key'=>$data['project_key']])
                    ->get();
            })
            ->first();
        if($template){
            $messageText = $template->text;
            foreach ($data['parameters']  as $key=>$param){
                $messageText = str_replace("{".$key."}", $param,$messageText);
            }
            switch ($data['channel']){
                case "sms":
                    $this->sendSms($template,$data['phones'],$messageText);
                    break;
                case "email":
                    $this->sendEmail($template,$data,$messageText);
                    break;
                case "telegram":
                    $this->sendTelegram($template,$messageText,$data);
                    break;
                case "whatsapp":
                    $this->sendWhatsApp($template,$data,$messageText);
                    break;
                case "viber":
                    $this->sendViber($template,$data);
                    break;
                case "fb":
                    $this->sendFb($template,$data);
                    break;
                default:
                    $this->responseStatus = false;
                    $this->responseCode = 400;
                    $this->responseMessage = "Неизвестный канал.";
            }
        }else{
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "Текст сообщения не найден.";
        }
        return response()->json([
            'success'=>$this->responseStatus,
            'message'=>$this->responseMessage
        ],$this->responseCode);
    }

    /**
     * @param Template $template
     * @param $phones
     * @param $message
     */
    private function  sendSms(Template $template,$phones,$message) {
        /*$mangoOffice = new MangoOffice("pvhroohvn4nz8k02jlgf2v6vikffsyb7","jipyxwjbrx0q7qih1pnubjfjtkpc9q9i");

        $command_id = "ID" . rand(10000000,99999999);
        $data = [
            'command_id' => $command_id,
            'from_extension' => '101',
            'to_number' => "37477632281",
            'text' => "Hi Jacob",
            'sms_sender' => ''
        ];
        $mangoOffice->putCmd('commands/sms', $data,$command_id);*/
        $project = $project = $this->getTemplatesProject($template,"sms",true);
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
                if($messenger->permission && $messenger->permission->alias === 'send'){
                    if($messenger->gateway->link === "https://smsc.ru") {
                        $configs = $this->getGatewaySettings($messenger);
                        $sendSMSJob = new SendSMSJob($phones,$message,$configs);
                        $job = $sendSMSJob->delay(now()->addSeconds(10));
                        dispatch($job);
                    }
                    if($messenger->gateway->link === "https://www.twilio.com"){
                        $configs = $this->getGatewaySettings($messenger);
                        $sendSMSJob = new SendTwilioSMSJob($phones,$message,$configs);
                        $job = $sendSMSJob->delay(now()->addSeconds(10));
                        dispatch($job);
                    }
                }
            }
            $this->responseStatus = true;
            $this->responseCode = 200;
            $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
        }else{
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан SMS шлюз с разрешением 'Отправка'.";
        }
    }

    private function  sendEmail(Template $template,$data,$emailText) {
        $project = $this->getTemplatesProject($template,"email",true);
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
                if($messenger->gateway->link === "https://sendpulse.com") {
                    $configs = $this->getGatewaySettings($messenger);
                    $sendEmailJob = new EmailSendPulseJob($configs);
                    $sendEmailJob->setData($data);
                    $sendEmailJob->setEmailText($emailText);
                    $job = $sendEmailJob->delay(now()->addSeconds(10));
                    dispatch($job);

                    $this->responseStatus = true;
                    $this->responseCode = 200;
                    $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
                }
            }
        }else{
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан Email шлюз.";
        }
    }

    /**
     * @param Template $template
     * @param $data
     * @param $emailText
     */
    private function  sendTelegram(Template $template,$messageText,$data) {
        $project = $this->getTemplatesProject($template,"telegram");
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
               $users = UsersChatInfo::query()
                   ->select('chat_id','bot')
                   ->where(['project_id'=>$messenger->project_id])
                   ->get();
               $gatewaySettings = $messenger->getGatewaySettings();
               foreach ($users as $user){
                   $botArray = json_decode($user->bot,true);
                   if($botArray['username'] ==  $gatewaySettings['username']){
                       $settings = [
                           "token"=>$gatewaySettings['token'],
                           "chat"=>$user->chat_id,
                           "text"=>$messageText,
                           "projectMessengerId"=>$messenger->id,
                       ];
                       $sendTelegramJob = new TelegramSendJob($settings,$template,$data);
                       $job = $sendTelegramJob->delay(now()->addSeconds(10));
                       dispatch($job);
                   }
               }
            }
            $this->responseStatus = true;
            $this->responseCode = 200;
            $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
        }else {
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан канал Telegram.";
        }
    }

    private function  sendWhatsApp(Template $template,$data,$messageText) {
        $project = $this->getTemplatesProject($template,"whatsapp",true);
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
                if($messenger->gateway->link === "https://www.twilio.com") {
                    $configs = $this->getGatewaySettings($messenger);
                    $sendSMSJob = new WhatsAppJob($data['phones'],$messageText,$configs,$template);
                    $job = $sendSMSJob->delay(now()->addSeconds(10));
                    dispatch($job);
                    $this->responseStatus = true;
                    $this->responseCode = 200;
                    $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
                }
            }
            $this->responseStatus = true;
            $this->responseCode = 200;
            $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
        } else {
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан канал WhatsApp.";
        }
    }

    private function sendViber(Template $template,$data){
        $project = $template->getTemplatesProject("viber");
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
                $gatewaySettings = $messenger->getGatewaySettings();
                $token = $gatewaySettings['token'];
                $users = $this->userChatInfoModel->getViberUsersInfo($token,$project->id);
                foreach ($users as $user){
                    if($user['chat_id']){
                        $settings = [
                            'receiver'=>$user['chat_id'],
                            'message_params'=>$data,
                            'auth_token'=>$token,
                            'template_variables'=>$data['parameters']
                        ];
                        $sendTelegramJob = new ViberSendJob($settings,$template);
                        $job = $sendTelegramJob->delay(now()->addSeconds(10));
                        dispatch($job);
                    }
                }
            }
            $this->responseStatus = true;
            $this->responseCode = 200;
            $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
        }else {
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан канал Viber.";
        }
    }

    private function sendFb(Template $template,$data){
        $project = $template->getTemplatesProject("fb");
        if(count($project->projectMessengers)){
            foreach ($project->projectMessengers as $messenger){
                $gatewaySettings = $messenger->getGatewaySettings();
                $appId = $gatewaySettings['app_id'];
                $token = $gatewaySettings['token'];
                $users = $this->userChatInfoModel->getFbUsers($appId);
                foreach ($users as $user){
                    if($user['user']){
                        $settings = [
                            'appId'=>$appId,
                            'message_params'=>$data,
                            'receiver'=>$user['chat_id'],
                            'auth_token'=>$token,
                            'template_variables'=>$data['parameters']
                        ];
                        $sendTelegramJob = new FacebookSendJob($settings,$template);
                        $job = $sendTelegramJob->delay(now()->addSeconds(10));
                        dispatch($job);
                    }
                }
            }
            $this->responseStatus = true;
            $this->responseCode = 200;
            $this->responseMessage = "Ваше сообшение успешо добавлено в очередь.";
        }else {
            $this->responseStatus = false;
            $this->responseCode = 400;
            $this->responseMessage = "В проекте не указан канал FB.";
        }
    }
    /**
     * @param ProjectMessenger $messenger
     * @return array
     */
    private function getGatewaySettings(ProjectMessenger $messenger){
        $gatewaySettings = $messenger->gatewaySettings()->select(['field_name','field_value'])->get();
        $configs = [];
        foreach ($gatewaySettings as $gatewaySetting){
            $configs[$gatewaySetting->field_name] = $gatewaySetting->field_value;
        }
        return $configs;
    }

    /**
     * @param Template $template
     * @param $channel
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|null|object
     */
    private function getTemplatesProject(Template $template,$channel,$gateway = false){
        return $template->project()->with(['projectMessengers'=>function($q) use($channel,$gateway){
            $q->select('project_messengers.*')
                //->join('messengers','project_messengers.messenger_id','=','messengers.id')
                ->join('permissions','project_messengers.permission_id','=','permissions.id')
                ->join('gateways','project_messengers.gateway_id','=','gateways.id')
                ->join('messengers','gateways.messenger_id','=','messengers.id')
                ->where('permissions.alias','send')
                ->where('messengers.alias',$channel);
            if($gateway){
                $q->whereNotNull('project_messengers.gateway_id');
            }
        }])->first();
    }

    public function test(){
        $telegram = new Api("594949732:AAE7_xrFytVJmc2QPeZ3VP0im1seLvGd6Sk");
        //$response = $telegram->setWebhook(["url"=>"https://e570f2fc.ngrok.io"]);

        $response = $telegram->getWebhookInfo();

        return  response()->json([$response],200);
    }
}
