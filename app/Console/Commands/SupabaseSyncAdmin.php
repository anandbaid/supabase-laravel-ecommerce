<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SupabaseSyncAdmin extends Command
{
    protected $signature = 'supabase:sync-admin {email} {password} {--name=Admin}';

    protected $description = 'Create or update an admin user in Supabase Auth and mirror it locally.';

    public function handle(SupabaseAuthService $supabaseAuth): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        try {
            $supabaseUser = $supabaseAuth->adminCreateUser($email, $password, ['name' => $name]);
        } catch (RuntimeException $e) {
            $this->error('Supabase Auth error: ' . $e->getMessage());
            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('Unexpected error: ' . $e->getMessage());
            return self::FAILURE;
        }

        try {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => null,
                    'role' => 'admin',
                    'supabase_uid' => $supabaseUser['id'] ?? null,
                ]
            );
        } catch (Throwable $e) {
            $this->error('Local user sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Admin user {$email} provisioned in Supabase Auth and synced locally.");
        return self::SUCCESS;
    }
}
