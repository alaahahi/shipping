<?php

namespace App\Console\Commands;

use App\Models\AppPage;
use App\Models\User;
use App\Services\PagePermissionService;
use Illuminate\Console\Command;

class DebugNavPermissions extends Command
{
    protected $signature = 'pages:debug-nav {user? : User id or email}';

    protected $description = 'Show navigation pages resolved for a user from DB permissions';

    public function handle(PagePermissionService $permissions): int
    {
        $user = $this->resolveUser($this->argument('user'));

        if (!$user) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        $this->info("User #{$user->id} ({$user->email}) type_id={$user->type_id}");
        $navPages = $permissions->getNavPagesForUser($user);

        $this->line('Nav pages: ' . count($navPages));
        foreach ($navPages as $page) {
            $this->line("  - {$page['label']} [{$page['route_name']}]");
        }

        $contract = AppPage::where('route_name', 'contract')->with('userTypes:id,name')->first();
        if ($contract) {
            $types = $contract->userTypes->pluck('name')->join(', ') ?: 'none';
            $this->newLine();
            $this->line("contract page types in DB: {$types}");
            $hasContract = collect($navPages)->contains(fn ($p) => ($p['route_name'] ?? '') === 'contract');
            $this->line('contract in nav: ' . ($hasContract ? 'YES' : 'NO'));
        }

        return self::SUCCESS;
    }

    protected function resolveUser(?string $identifier): ?User
    {
        if ($identifier) {
            if (is_numeric($identifier)) {
                return User::find((int) $identifier);
            }

            return User::where('email', $identifier)->orWhere('name', $identifier)->first();
        }

        return User::where('type_id', 1)->orderBy('id')->first() ?? User::find(1);
    }
}
