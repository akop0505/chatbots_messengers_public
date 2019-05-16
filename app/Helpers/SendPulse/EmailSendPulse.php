<?php

namespace App\Http\Helpers\SendPulse;

use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;
use Sendpulse\RestApi\Storage\SessionStorage;

class EmailSendPulse
{
    private $api_user_id = null;
    private $api_secret = null;
    private $message = [];

    public function __construct($api_user_id,$api_secret)
    {
        $this->api_user_id = $api_user_id;
        $this->api_secret = $api_secret;
        $this->message = [
            'html' => 'sdfsdfsdfsdf',
            'text' => '',
            'subject' => 'sdfsdfsdf',
            'from' => [],
            'to' => []
        ];
    }

    public function setHtml($html){
        $this->message['html'] = $html;
    }

    public function setText($text){
        $this->message['text'] = $text;
    }

    public function setSubject($subject){
        $this->message['subject'] = $subject;
    }

    public function setFrom($from){
        $this->message['from'] = $from;
    }

    public function setTo($to){
        $this->message['to'] = $to;
    }

    public function build($from,$to,$text){
        $this->setText($text);
        $this->setFrom($from);
        $this->setTo($to);
    }

    public function send()
    {
        $SPApiClient = new ApiClient($this->api_user_id, $this->api_secret, new SessionStorage());
        if(!count($this->message['from']) || !count($this->message['to']))
        {
            Log::error("Sending Email Error : ",$this->message);
            return;
        }
        $result = $SPApiClient->smtpSendMail($this->message);
        Log::error("Sending Email MESSAGE OBJECT : ",$this->message);
        Log::error("Sending Email Result : ",[$result]);
    }
}