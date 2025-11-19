<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateSyncToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:generate-token {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Sanctum token for offline application sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        if (!$userId) {
            // Get first admin user
            $user = User::first();
            if (!$user) {
                $this->error('No users found in database. Please create a user first.');
                return 1;
            }
            $this->info('Using user: ' . $user->name . ' (ID: ' . $user->id . ')');
        } else {
            $user = User::find($userId);
            if (!$user) {
                $this->error('User not found with ID: ' . $userId);
                return 1;
            }
        }

        // Revoke existing sync tokens
        $user->tokens()->where('name', 'offline-sync')->delete();
        $this->info('Revoked existing sync tokens for user: ' . $user->name);

        // Generate new token
        $token = $user->createToken('offline-sync', ['penjualan:sync'])->plainTextToken;

        $this->newLine();
        $this->info('==================================================');
        $this->info('Sanctum Token Generated Successfully!');
        $this->info('==================================================');
        $this->newLine();
        $this->line('User: ' . $user->name . ' (ID: ' . $user->id . ')');
        $this->line('Token Name: offline-sync');
        $this->newLine();
        $this->warn('Copy this token and add to your OFFLINE app .env file:');
        $this->newLine();
        $this->line('MASTER_API_TOKEN=' . $token);
        $this->newLine();
        $this->warn('⚠️  This token will only be shown once. Please save it now!');
        $this->info('==================================================');
        $this->newLine();

        return 0;
    }
}
