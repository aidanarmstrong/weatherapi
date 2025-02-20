<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;

class DispatchWelcomeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:welcome-email {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a queued job to send a welcome email to a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::findOrFail($this->argument('user_id'));

        SendWelcomeEmail::dispatch($user);

        $this->info('Welcome email job dispatched successfully! '. $user->email);
    }
}
