<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin {email : Email address of the user to promote}';

    protected $description = 'Promote an existing user to the admin role';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->update(['role' => 'admin']);

        $this->info("{$user->email} is now an admin.");

        return self::SUCCESS;
    }
}
