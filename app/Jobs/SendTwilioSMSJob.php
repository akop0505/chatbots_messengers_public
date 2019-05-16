<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SendTwilioSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $phones = [];
    private $message = "";
    private $sid = null;
    private $token = null;
    private $from = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($phones,$message,$configs)
    {
        $this->phones  = $phones;
        $this->message =  $message;
        $this->sid =   $configs['sid'] ? : null;
        $this->token = $configs['token'] ? : null;
        $this->from = $configs['phone'] ? : null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $twilio = new Client($this->sid, $this->token);

        try{
            $resultArray = [];
            foreach ($this->phones as $to){
                $message = $twilio->messages
                    ->create($to,
                        array(
                            "body" => $this->message,
                            "from" => $this->from
                        )
                    );
                array_push($resultArray,$message);
            }
            Log::info("TwilioSms: send message result = ",$resultArray);
        }catch (\Exception $exception){
            Log::error("TwilioSms: send message ERROR = ",[$exception->getMessage()]);
        }
    }
}
