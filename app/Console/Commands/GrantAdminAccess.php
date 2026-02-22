<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantAdminAccess extends Command
{
    protected $signature = 'admin:grant {email}';

    protected $description = 'Grant admin access to a user by email';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            $this->line('The user must have an existing account in the app before being granted admin access.');

            return Command::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info("{$user->name} ({$email}) is already an admin.");

            return Command::SUCCESS;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("Admin access granted to {$user->name} ({$email}).");

        return Command::SUCCESS;
    }
}
