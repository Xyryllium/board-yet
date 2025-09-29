<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupExpiredPasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-reset:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired password reset tokens (older than 60 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedCount = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subMinutes(60))
            ->delete();

        $this->info("Cleaned up {$deletedCount} expired password reset tokens.");

        return Command::SUCCESS;
    }
}
