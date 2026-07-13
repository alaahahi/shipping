<?php

namespace App\Services;

use App\Models\AppPage;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PagePermissionService
{
    public function isPermissionManager(User $user): bool
    {
        $typeId = (int) $user->type_id;
        $userId = (int) $user->id;

        return $typeId === 1 || $typeId === 6 || $userId === 1;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $this->isPermissionManager($user);
    }

    public function cacheKeyForUser(User $user): string
    {
        return 'app_pages_user_' . $user->id . '_type_' . $user->type_id;
    }

    public function clearUserCache(User $user): void
    {
        Cache::forget($this->cacheKeyForUser($user));
    }

    public function clearAllCaches(): void
    {
        Cache::flush();
    }

    public function getAllowedRouteNamesForUser(User $user): array
    {
        if ($this->isSuperAdmin($user)) {
            return AppPage::query()
                ->where('is_active', true)
                ->whereNotNull('route_name')
                ->orderBy('sort_order')
                ->pluck('route_name')
                ->all();
        }

        return Cache::remember($this->cacheKeyForUser($user), 300, function () use ($user) {
            return AppPage::query()
                ->where('is_active', true)
                ->whereNotNull('route_name')
                ->whereHas('userTypes', fn ($query) => $query->where('user_type.id', $user->type_id))
                ->orderBy('sort_order')
                ->pluck('route_name')
                ->all();
        });
    }

    public function getNavPagesForUser(User $user): array
    {
        $query = AppPage::query()
            ->where('is_active', true)
            ->orderBy('sort_order');

        if (!$this->isSuperAdmin($user)) {
            $query->whereHas('userTypes', fn ($q) => $q->where('user_type.id', $user->type_id));
        }

        return $query->get(['id', 'slug', 'route_name', 'path', 'label', 'nav_group', 'sort_order'])
            ->map(fn (AppPage $page) => [
                'slug' => $page->slug,
                'route_name' => $page->route_name,
                'path' => $page->path,
                'label' => $page->label,
                'nav_group' => $page->nav_group,
                'sort_order' => $page->sort_order,
            ])
            ->all();
    }

    public function canAccessRoute(User $user, ?string $routeName): bool
    {
        if (!$routeName) {
            return true;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return in_array($routeName, $this->getAllowedRouteNamesForUser($user), true);
    }
}
