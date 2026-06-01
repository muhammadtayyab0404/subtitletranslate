<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public $digit;

    /**
     * Create a new job instance.
     */
    public function __construct($aaa)
    {
        $this->digit =$aaa;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    Log::info('user checked successfully', ['aa'=>$this->digit]);
        //
    }
}
