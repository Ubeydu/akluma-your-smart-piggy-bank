<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RevokeAdminAccess extends Command
{
    protected $signature = 'admin:revoke {email}';

    protected $description = 'Revoke admin access from a user by email';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            $this->line('The user must have an existing account in the app.');

            return Command::FAILURE;
        }

        if (! $user->isAdmin()) {
            $this->info("{$user->name} ({$email}) is not an admin.");

            return Command::SUCCESS;
        }

        $user->is_admin = false;
        $user->save();

        $this->info("Admin access revoked from {$user->name} ({$email}).");

        return Command::SUCCESS;
    }
}
