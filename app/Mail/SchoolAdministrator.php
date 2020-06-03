<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class SchoolAdministrator extends Mailable
{
   // use Queueable, SerializesModels;
   use SendGrid;

    protected $email;

    protected $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email,$code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@royal7.com')
        ->subject('School Admininstratoer Email Confirmation')
        ->markdown('emails.admin.confirmation')
        ->with([
            'link' => 'https://royal7.netlify.app/confirm/'.$this->email.'/'.$this->code,
            'email' => $this->email,
        ]) // added the sendgrid params
        ->sendgrid([
            'personalizations' => [
                [
                    'substitutions' => [
                        ':myname' => 's-ichikawa',
                    ],
                ],
            ],
        ]);
    }
}