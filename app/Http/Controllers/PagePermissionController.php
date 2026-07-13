<?php

namespace App\Http\Controllers;

use App\Models\AppPage;
use App\Models\UserType;
use App\Services\AppPageDefaults;
use App\Services\PagePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PagePermissionController extends Controller
{
    public function __construct(protected PagePermissionService $permissions)
    {
    }

    protected function authorizeManager(): void
    {
        $user = Auth::user();
        if (!$user || !$this->permissions->isPermissionManager($user)) {
            abort(403, 'غير مسموح الوصول');
        }
    }

    public function index()
    {
        $this->authorizeManager();

        return Inertia::render('Admin/PagePermissions');
    }

    public function getData()
    {
        $this->authorizeManager();

        $pages = AppPage::with(['userTypes:id,name'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $userTypes = UserType::withCount('appPages')
            ->orderBy('id')
            ->get(['id', 'name']);

        return Response::json([
            'pages' => $pages,
            'userTypes' => $userTypes,
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'nav_group' => 'required|in:main,more',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'user_type_ids' => 'array',
            'user_type_ids.*' => 'integer|exists:user_type,id',
        ]);

        $slug = $this->makeUniqueSlug($validated['route_name'] ?? $validated['label']);

        $page = AppPage::create([
            'slug' => $slug,
            'label' => $validated['label'],
            'route_name' => $validated['route_name'] ?? null,
            'path' => $validated['path'] ?? null,
            'nav_group' => $validated['nav_group'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $page->userTypes()->sync($validated['user_type_ids'] ?? []);
        $this->permissions->clearAllCaches();

        return Response::json($page->load('userTypes:id,name'), 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeManager();

        $page = AppPage::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'nav_group' => 'required|in:main,more',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'user_type_ids' => 'array',
            'user_type_ids.*' => 'integer|exists:user_type,id',
        ]);

        $page->update([
            'label' => $validated['label'],
            'route_name' => $validated['route_name'] ?? null,
            'path' => $validated['path'] ?? null,
            'nav_group' => $validated['nav_group'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (array_key_exists('user_type_ids', $validated)) {
            $page->userTypes()->sync($validated['user_type_ids']);
        }

        $this->permissions->clearAllCaches();

        return Response::json($page->load('userTypes:id,name'), 200);
    }

    public function destroy(int $id)
    {
        $this->authorizeManager();

        $page = AppPage::findOrFail($id);
        $page->delete();

        $this->permissions->clearAllCaches();

        return Response::json(['success' => true], 200);
    }

    public function syncUserTypePages(Request $request, int $userTypeId)
    {
        $this->authorizeManager();

        $userType = UserType::findOrFail($userTypeId);

        $validated = $request->validate([
            'page_ids' => 'array',
            'page_ids.*' => 'integer|exists:app_pages,id',
        ]);

        $userType->appPages()->sync($validated['page_ids'] ?? []);
        $this->permissions->clearAllCaches();

        return Response::json($userType->load(['appPages:id,label,route_name']), 200);
    }

    public function importDefaults(AppPageDefaults $defaults)
    {
        $this->authorizeManager();

        $result = $defaults->importMissingOnly();
        $this->permissions->clearAllCaches();

        return Response::json([
            'message' => "تم استيراد {$result['created']} صفحة، وتخطي {$result['skipped']} موجودة مسبقاً",
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ], 200);
    }

    protected function makeUniqueSlug(string $source): string
    {
        $base = Str::slug($source, '_');
        if ($base === '') {
            $base = 'page';
        }

        $slug = $base;
        $counter = 1;

        while (AppPage::where('slug', $slug)->exists()) {
            $slug = $base . '_' . $counter;
            $counter++;
        }

        return $slug;
    }
}
