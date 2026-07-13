<?php

namespace App\Services;

use App\Models\AppPage;
use App\Models\User;
use App\Services\AppPageDefaults;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PagePermissionService
{
    public function isPermissionManager(User $user): bool
    {
        $typeId = (int) $user->type_id;
        $userId = (int) $user->id;

        return $typeId === 1 || $typeId === 6 || $userId === 1;
    }

    public function cacheKeyForUser(User $user): string
    {
        return 'app_pages_v4_user_' . $user->id . '_type_' . $user->type_id;
    }

    public function clearUserCache(User $user): void
    {
        Cache::forget($this->cacheKeyForUser($user));
    }

    public function clearAllCaches(): void
    {
        Cache::flush();
    }

    public function pageIdsForType(int $typeId): array
    {
        $pageIds = DB::table('app_page_user_type')
            ->where('user_type_id', $typeId)
            ->pluck('app_page_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($pageIds !== []) {
            return $pageIds;
        }

        return app(AppPageDefaults::class)->defaultPageIdsForType($typeId);
    }

    public function getAllowedRouteNamesForUser(User $user): array
    {
        return Cache::remember($this->cacheKeyForUser($user), 300, function () use ($user) {
            $pageIds = $this->pageIdsForType((int) $user->type_id);

            if ($pageIds === []) {
                $routes = [];
            } else {
                $routes = AppPage::query()
                    ->where('is_active', true)
                    ->whereNotNull('route_name')
                    ->whereIn('id', $pageIds)
                    ->orderBy('sort_order')
                    ->pluck('route_name')
                    ->all();
            }

            if ($this->isPermissionManager($user) && !in_array('pagePermissions', $routes, true)) {
                $routes[] = 'pagePermissions';
            }

            return $routes;
        });
    }

    public function getNavPagesForUser(User $user): array
    {
        $pageIds = $this->pageIdsForType((int) $user->type_id);

        if ($pageIds === []) {
            $pages = [];
        } else {
            $pages = AppPage::query()
                ->where('is_active', true)
                ->whereIn('id', $pageIds)
                ->orderBy('sort_order')
                ->get(['id', 'slug', 'route_name', 'path', 'label', 'nav_group', 'sort_order'])
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

        if ($this->isPermissionManager($user) && !$this->navPagesContainRoute($pages, 'pagePermissions')) {
            $pages[] = [
                'slug' => 'page_permissions',
                'route_name' => 'pagePermissions',
                'path' => '/page-permissions',
                'label' => 'صلاحيات الصفحات',
                'nav_group' => 'more',
                'sort_order' => 999,
            ];

            usort($pages, fn ($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
        }

        return $pages;
    }

    public function canAccessRoute(User $user, ?string $routeName): bool
    {
        if (!$routeName) {
            return true;
        }

        if ($routeName === 'pagePermissions' && $this->isPermissionManager($user)) {
            return true;
        }

        $pageExists = AppPage::query()
            ->where('is_active', true)
            ->where('route_name', $routeName)
            ->exists();

        if (!$pageExists) {
            return true;
        }

        return in_array($routeName, $this->getAllowedRouteNamesForUser($user), true);
    }

    protected function navPagesContainRoute(array $pages, string $routeName): bool
    {
        foreach ($pages as $page) {
            if (($page['route_name'] ?? null) === $routeName) {
                return true;
            }
        }

        return false;
    }
}
