<?php

namespace App\Models;

use App\Repositories\AnswerableMessageRepository;
use AppReplyMessageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AnswerableMessage extends Model implements AnswerableMessageRepository
{
    /*states*/
    const WAITING = 'waiting';
    const ANSWERED = 'answered';
    const BOT_SIMPLE = 'bot_simple';
    const USER_SIMPLE = 'user_simple';
    const USER_ANSWER = 'user_answer';

    /*channels*/
    const CHANNEL_TELEGRAM = 'telegram';
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_VIBER = 'viber';
    const CHANNEL_FB = 'fb';

    const TYPE_FILE = 'file';
    const TYPE_TEXT = 'text';

    protected $fillable = [
        'parent_id',
        'message_id',
        'template_id',
        'template_params',
        'chat_id',
        'chat_user_id',
        'state',
        'answer',
        'type',
        'file_json',
        'file_path',
        'channel',
        'bot_username',
        'message_params',
    ];

    protected $appends = [
        'message'
    ];

    protected $casts = [
        'message_params' => 'array',
    ];

    public function setMessageParamsAttribute($value)
    {
        $this->attributes['message_params'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getMessageAttribute()
    {
        if (request()->route()->getName() == "dialog.messages") {
            $template = $this->template;
            if (!$template) {
                return "";
            }
            return AppReplyMessageService::prepareTemplateWithMenu($template, json_decode($this->template_params, true));
        }
        return "";
    }

    public  function getFileJsonAttribute($value){
        return json_decode($value,true);
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    public function getTelegramsLastQuestion($chatId, $botUsername)
    {
        return $this->getLastQuestion($chatId, $botUsername, self::CHANNEL_TELEGRAM);
    }

    public function getLastQuestion($chatId, $botUsername, $channel)
    {
        return AnswerableMessage::query()
            ->where([
                'chat_id' => $chatId,
                'channel' => $channel,
                'bot_username' => $botUsername
            ])
            ->orderBy('message_id', 'desc')
            ->first();
    }

    public function getViberLastQuestion($chatId, $botUsername)
    {
        return $this->getLastQuestion($chatId, $botUsername, self::CHANNEL_VIBER);
    }

    public function getFbLastQuestion($sender, $appId)
    {
        return AnswerableMessage::query()
            ->where([
                'chat_id' => $sender,
                'channel' => self::CHANNEL_FB,
                'bot_username' => $appId
            ])
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getWhatsappLastQuestion($chatId, $from)
    {
        AnswerableMessage::query()
            ->where([
                'chat_id' => $chatId,
                'chat_user_id' => $from,
                'channel' => AnswerableMessage::CHANNEL_WHATSAPP
            ])
            ->orderBy('id', 'desc')
            ->first();
    }

    public function createTelegramAnswerableQuestion($data)
    {
        $this->create([
            "message_id" => $data['message_id'],
            "message_params" => $data['message_params'] ?? null,
            "template_id" => $data['template_id'],
            "template_params" => $data['template_params'],
            "chat_id" => $data['chat_id'],
            "chat_user_id" => isset($data['chat_user_id'])?$data['chat_user_id']:null,
            "state" => AnswerableMessage::WAITING,
            "channel" => AnswerableMessage::CHANNEL_TELEGRAM,
            "bot_username" => $data['bot_username']
        ]);

    }

    public function createViberAnswerableQuestion($data)
    {
        $this->create([
            "message_id" => $data['message_id'],
            "message_params" => $data['message_params'] ?? null,
            "template_id" => $data['template_id'],
            "chat_id" => $data['receiver'],
            "state" => AnswerableMessage::WAITING,
            "channel" => AnswerableMessage::CHANNEL_VIBER,
            "bot_username" => $data['token']
        ]);

    }

    public function createFbAnswerableQuestion($data)
    {
        $this->create([
            "message_id" => $data['message_id'],
            "message_params" => $data['message_params'] ?? null,
            "template_id" => $data['template_id'],
            "chat_id" => $data['receiver'],
            "state" => AnswerableMessage::WAITING,
            "channel" => AnswerableMessage::CHANNEL_FB,
            "bot_username" => $data['appId']
        ]);

    }

    public function getAll($channel, $filter)
    {
        return $this->query()
            ->where(["channel" => $channel])
            ->where($filter)
            ->orderBy('id')
            ->get();
    }

    public function createSimpleMessage($data, $channel, $state = null)
    {
        $message = null;
        if(isset($data['message_id'])){
            $message = $this->query()
                ->where(['message_id'=>$data['message_id']])
                ->first();
        }
        if($message){
            return $message;
        }
        return $this->create([
            "message_id"=>isset($data['message_id']) ? $data['message_id'] : null,
            "chat_id" => $data['chat_id'],
            "state" => $state ?: AnswerableMessage::USER_SIMPLE,
            "channel" => $channel,
            "answer" => $data['answer'],
            "bot_username" => $data['bot_username']
        ]);
    }

    public function userAnswer()
    {
        return $this->hasOne(self::class, 'parent_id', 'id');
    }

    public function updateSeen($ides){
        $this->query()
            ->whereIn('id',$ides)
            ->update(["seen"=>true]);
    }
}
