<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

class EmailNotify extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $recepient_list = [];
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $to = null)
    {
        $this->subject = $subject;
        $this->message = $message;

        if(!empty($to)) {
            $this->recepient_list = is_string($to) ? ['email' => $to] : [$to];
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
			->from('info@axa-partners.ru', 'Sabina')
			->view('emails.notify', ['text' => $this->message])
            ->subject($this->subject);
		//	->with([
		//			'message' => $this->message,
		//		]);
    }

    /**
     * Add the sender to the message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildFrom($message)
    {
        if (! empty($this->from)) {
            $message->from($this->from[0]['email'], $this->from[0]['name']);
        }

        return $this;
    }

    /**
     * Add all of the recipients to the message.
     *
     * @param  \Illuminate\Mail\Message  $message
     * @return $this
     */
    protected function buildRecipients($message)
    {
        foreach (['to', 'cc', 'bcc', 'replyTo'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $message->{$type}($recipient['email'], $recipient['name']);
            }
        }

        return $this;
    }

    /**
     * Set the recipients of the message.
     *
     * All recipients are stored internally as [['name' => ?, 'address' => ?]]
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return $this
     */
    protected function setAddress($address, $name = null, $property = 'to')
    {
        foreach ($this->addressesToArray($address, $name) as $recipient) {
            $recipient = $this->normalizeRecipient($recipient);

            $this->{$property}[] = [
                'name' => $recipient->name ?? null,
                'email' => $recipient->email,
            ];
        }

        return $this;
    }

    /**
     * Determine if the given recipient is set on the mailable.
     *
     * @param  object|array|string  $address
     * @param  string|null  $name
     * @param  string  $property
     * @return bool
     */
    protected function hasRecipient($address, $name = null, $property = 'to')
    {
        $expected = $this->normalizeRecipient(
            $this->addressesToArray($address, $name)[0]
        );

        $expected = [
            'name' => $expected->name ?? null,
            'email' => $expected->email,
        ];

        return collect($this->{$property})->contains(function ($actual) use ($expected) {
            if (! isset($expected['name'])) {
                return $actual['email'] == $expected['email'];
            }

            return $actual == $expected;
        });
    }

    public function send(MailerContract $mailer)
    {
        Container::getInstance()->call([$this, 'build']);

        $ap_api_client = new ApiClient(env('SENDPULSE_ID'), env('SENDPULSE_SECRET'), new FileStorage());

        $data = [
            'html'    => $this->render(),
            'text'    => $this->message,
            'subject' => $this->subject,
            'from' => $this->from[0],
            'to'   => [
                $this->recepient_list
            ]
        ];
        $result = $ap_api_client->smtpSendMail($data);
        return $result;
    }


}
