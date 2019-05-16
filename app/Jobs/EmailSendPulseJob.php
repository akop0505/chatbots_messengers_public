<?php

namespace App\Jobs;

use App\Http\Helpers\SendPulse\EmailSendPulse;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EmailSendPulseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $api_user_id = null;
    private $api_secret = null;
    public $text = null;
    public $data = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->api_user_id = $config['api_user_id'];
        $this->api_secret   = $config['api_secret'];
    }

    /**
     * @param $data
     */
    public function setData($data){
        $this->data = $data;
    }

    public function setEmailText($text){
        $this->text = $text;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sendPulse = new EmailSendPulse($this->api_user_id,$this->api_secret);
        if(isset($this->data['from'])){
            $sendPulse->setFrom($this->data['from']);
        }
        if(isset($this->data['to'])){
            $sendPulse->setTo($this->data['to']);
        }
        if($this->text){
            $sendPulse->setText($this->text);
        }
        $sendPulse->send();
    }
}
