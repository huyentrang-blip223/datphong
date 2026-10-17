<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FoundationSchemaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class PrepareDeployment extends Command
{
    protected $signature = 'dt07:prepare-deployment {--schema=}';

    protected $description = 'Initialize an empty MySQL schema, apply migrations and bootstrap the first admin safely';

    public function handle(FoundationSchemaService $service): int
    {
        $path = $this->option('schema') ?: base_path('database-schema.sql');
        $fresh = $service->initialize($path);
        if ($this->call('migrate', ['--force' => true]) !== 0) {
            return self::FAILURE;
        }
        if (! User::where('role', 'admin')->exists()) {
            $email = env('BOOTSTRAP_ADMIN_EMAIL');
            $password = env('BOOTSTRAP_ADMIN_PASSWORD');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 12) {
                $this->error('Set BOOTSTRAP_ADMIN_EMAIL and a password of at least 12 characters.');

                return self::FAILURE;
            }
            User::create(['email' => strtolower($email), 'full_name' => 'Quản trị viên', 'role' => 'admin', 'status' => 'active', 'password_hash' => Hash::make($password)]);
        }
        if ($fresh && filter_var(env('DEPLOY_DEMO_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->call('db:seed', ['--class' => 'DeploymentDemoSeeder', '--force' => true]);
        }
        $this->info('Deployment schema prepared; existing accounts/data preserved.');

        return self::SUCCESS;
    }
}
