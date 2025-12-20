<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\VerifyEmailApi;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMailConfirmationJob implements ShouldQueue
{
    use Queueable, SerializesModels;
    public int $userId;
    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        //
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if ($user) {
            $user->notify(new VerifyEmailApi());
        }
    }
}
