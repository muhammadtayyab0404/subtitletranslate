<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class MailService
{
    public static function send($tomail, $subject, $message,$otp)
    {
      $request= Mail::to($tomail)->send(new WelcomeMail($message,$subject,$otp));

    }
}

?>