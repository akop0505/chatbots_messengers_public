<?php

namespace App\Jobs;

use App\Http\Helpers\SMSCenter\SMSCenter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $phones = [];
    private $message = "";
    private $login = "";
    private $password = "";
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phones,$message,$configs)
    {
        $this->phones = $phones;
        $this->message = $message;
        $this->login = $configs['login'] ?:null;
        $this->password = $configs['password'] ?:null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $smsClient = new SMSCenter($this->login,$this->password);
        $result = $smsClient->send($this->phones,$this->message);

        Log::info("login",[$this->login]);
        Log::info("password",[$this->password]);
        Log::info("phones",$this->phones);
        Log::info("message",[$this->message]);
        Log::info("SMSC_Result",[$result]);
    }
}
