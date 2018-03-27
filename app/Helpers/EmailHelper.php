<?php

namespace App\Helpers;

use App\Jobs\SendWelcomeEmailJob;
use Illuminate\Support\Facades\Queue;

class EmailHelper
{
    public static function SendWelcomeEmail($to, $hash)
    {
        Queue::push(new SendWelcomeEmailJob($to, $hash));
    }
}