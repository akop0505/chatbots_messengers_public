<?php

namespace App\Jobs;

use App\Models\AnswerableMessage;
use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use AppReplyMessageService;
use AppFacebookService;

class FacebookSendJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $receiver = null;
    private $appId = null;
    private $auth_token = null;
    private $template_variables = null;
    private $template = null;
    private $message_params = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($settings,Template $template)
    {
        $this->message_params = $settings['message_params'];
        $this->appId = $settings['appId'];
        $this->receiver = $settings['receiver'];
        $this->auth_token = $settings['auth_token'];
        $this->template_variables = $settings['template_variables'];
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data =  AppReplyMessageService::prepareTemplateWithMenu($this->template,$this->template_variables);
        $response = AppFacebookService::sendToMember($this->auth_token,$this->receiver,$data['text']);
        if($response['message_id']){
            if(isset($data['shouldSave']) && $data['shouldSave'] === true){
                $model  = [
                    "message_id"=>$response['message_id'],
                    "message_params"=>$this->message_params,
                    "template_id"=>$this->template->id,
                    "receiver"=>$this->receiver,
                    "appId"=>$this->appId
                ];
                (new AnswerableMessage())->createFbAnswerableQuestion($model);
            }
        }
    }
}
