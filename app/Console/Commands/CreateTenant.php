<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create {name : The name of the organization} {subdomain : The subdomain for routing} {--plan=free : The subscription plan (free, basic, premium)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tenant and company profile under single-database multitenancy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $subdomain = strtolower($this->argument('subdomain'));
        $plan = strtolower($this->option('plan'));

        if (Tenant::where('subdomain', $subdomain)->exists()) {
            $this->error("Subdomain '{$subdomain}' is already taken!");

            return 1;
        }

        // 1. Create the tenant routing context
        $tenant = Tenant::create([
            'name' => $name,
            'subdomain' => $subdomain,
        ]);

        // 2. Create the associated company profile
        Company::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'subscription_plan' => $plan,
        ]);

        $this->info("Tenant '{$name}' created successfully!");
        $this->line("Subdomain: <info>{$subdomain}</info>");
        $this->line("Workspace URL: <info>http://{$subdomain}.hrportal.localhost:8000/login</info>");

        return 0;
    }
}
