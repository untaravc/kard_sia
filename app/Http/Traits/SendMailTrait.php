<?php

namespace App\Http\Traits;

use App\Mail\EmailConfirmationMail;
use Illuminate\Support\Facades\Mail;

trait SendMailTrait
{
    public function send($mail)
    {
        switch ($mail->label){
            case 'email-confirmation':
                return $this->email_confirmation($mail);
        }
    }

    public function email_confirmation($mail)
    {
        Mail::to($mail->email)->send(new EmailConfirmationMail($mail));
    }
}
