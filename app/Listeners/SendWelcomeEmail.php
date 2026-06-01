<?php

namespace App\Listeners;
use Illuminate\Foundation\Bus\Dispatchable;
 use App\Events\userRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(userRegistered $event): void
    {
    Log::info('Checking The event Listner'.$event->user);

        //
    }
}
