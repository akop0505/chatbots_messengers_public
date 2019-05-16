<?php

namespace App\Telegram;

use App\Models\GatewaySetting;
use App\Models\ProjectMessenger;
use App\Models\UsersChatInfo;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;
use Telegram;


/**
 * Class TestCommand.
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Welcome!';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        try{
            $this->replyWithChatAction(['action'=>Actions::TYPING]);

            /*$this->replyWithMessage(['text'=>json_encode($this->telegram->getMe())]);
            $this->replyWithMessage(['text'=>json_encode($this->getUpdate())]);*/
            $me = Telegram::getMe();
            $message = $this->getUpdate()['message'];
            $model = UsersChatInfo::query()->where([
                'bot_id'=>$me['username'],
                'chat_id'=>$message['from']['id'],
                'channel'=>'telegram'
            ])->first();

            if(!$model){
                $model = new UsersChatInfo();
            }

            $settings = GatewaySetting::query()
                ->select(['G2.project_messenger_id','G2.field_value'])
                ->join('gateway_settings as G2','gateway_settings.project_messenger_id','=','G2.project_messenger_id')
                ->where([
                    'gateway_settings.field_value'=>Telegram::getAccessToken(),
                    'G2.field_name'=>'username'
                ])->first()->toArray();
            Log::debug(Telegram::getAccessToken(),[$settings]);
            /*return;*/
            $projectM = ProjectMessenger::query()
                ->select(['project_messengers.project_id'])
                ->where(['id'=>$settings['project_messenger_id']])->first();

            if($projectM){
                $model->setAttribute('project_id',$projectM->project_id);
            }

            $model->setAttribute('bot_id',$me['username']);
            $model->setAttribute('bot',json_encode($me,JSON_UNESCAPED_UNICODE));

            $model->setAttribute('chat_id',$message['from']['id']);
            $model->setAttribute('user',$message);

            $model->setAttribute('channel','telegram');

            $model->save();

            $this->replyWithMessage(['text'=>'You have been subscribed to notifications.']);

        }catch (\Exception $e){
            $this->replyWithMessage(["text"=>$e->getMessage()]);
        }
        return true;
    }
}
