<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixInvalidEmails extends Command
{
    protected $signature = 'users:fix-invalid-emails';

    protected $description = 'Supprime les espaces des adresses email invalides';

    public function handle(): int
    {
        $this->info('Recherche des emails invalides...');

        $users = User::all();
        $fixed = 0;

        foreach ($users as $user) {
            $originalEmail = $user->email;
            $cleanedEmail = str_replace(' ', '', $originalEmail);

            if ($originalEmail !== $cleanedEmail) {
                $this->info("Correction: {$originalEmail} -> {$cleanedEmail}");
                $user->email = $cleanedEmail;
                $user->save();
                $fixed++;
            }
        }

        $this->info("✓ {$fixed} email(s) corrigé(s)");

        return 0;
    }
}
