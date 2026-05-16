<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;

class E2eResetOrgCommand extends Command
{
    protected $signature = 'e2e:reset-org {--email=alice@demo.test : User email to reset} {--org-id=1 : Organization ID to set as current}';

    protected $description = 'Reset a demo user\'s current organization (E2E test helper)';

    public function handle(): int
    {
        $email = $this->option('email');
        $orgId = (int) $this->option('org-id');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User not found: {$email}");

            return self::FAILURE;
        }

        $org = Organization::find($orgId);

        if (! $org) {
            $this->error("Organization not found: {$orgId}");

            return self::FAILURE;
        }

        $user->update(['current_organization_id' => $orgId]);

        $this->info("Reset {$email} to org: {$org->name} (ID {$orgId})");

        return self::SUCCESS;
    }
}
