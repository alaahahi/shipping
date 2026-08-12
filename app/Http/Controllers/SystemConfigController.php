<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemConfigController extends Controller
{
    /**
     * جلب إعدادات النظام
     */
    public function index()
    {
        SystemConfig::ensureMediaColumns();
        SystemConfig::ensureWhatsAppColumns();

        $config = SystemConfig::first();

        if (! $config) {
            $config = SystemConfig::create([
                'first_title_ar' => '',
                'first_title_kr' => '',
                'second_title_ar' => '',
                'second_title_kr' => '',
                'third_title_ar' => '',
                'third_title_kr' => '',
            ]);
        }

        $payload = $config->toArray();
        $payload['car_expenses_wallet'] = $this->resolveCarExpensesWallet(
            $payload['car_expenses_wallet_user_id'] ?? null
        );

        return Response::json($payload, 200);
    }

    /**
     * تحديث إعدادات النظام
     */
    public function update(Request $request)
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }
        $validator = Validator::make($request->all(), [
            'first_title_ar' => 'nullable|string|max:255',
            'first_title_kr' => 'nullable|string|max:255',
            'second_title_ar' => 'nullable|string|max:255',
            'second_title_kr' => 'nullable|string|max:255',
            'third_title_ar' => 'nullable|string|max:255',
            'third_title_kr' => 'nullable|string|max:255',
            'default_price_s' => 'nullable|array',
            'default_price_p' => 'nullable|array',
            'usd_to_aed_rate' => 'nullable|numeric|min:0',
            'usd_to_dinar_rate' => 'nullable|numeric|min:0',
            'contract_terms' => 'nullable|array',
            'contract_terms_2' => 'nullable|array',
            'external_contract_terms' => 'nullable|array',
            'external_contract_terms_2' => 'nullable|array',
            'contract_template' => 'nullable|in:1,2,3',
            'contract_currency' => 'nullable|in:usd,dinar',
            'primary_color' => 'nullable|string|max:20',
            'wa_enabled' => 'nullable|boolean',
            'wa_tenant' => 'nullable|string|max:100',
            'wa_base_url' => 'nullable|string|max:255',
            'wa_created_by' => 'nullable|string|max:100',
            'wa_notify_client_debt' => 'nullable|boolean',
            'wa_notify_payment_receipt' => 'nullable|boolean',
            'wa_notify_car_added' => 'nullable|boolean',
            'wa_msg_client_debt' => 'nullable|string|max:4096',
            'wa_msg_payment_receipt' => 'nullable|string|max:4096',
            'wa_msg_car_added' => 'nullable|string|max:4096',
            'car_expenses_wallet_user_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = SystemConfig::first();

        if (! $config) {
            $config = SystemConfig::create([
                'first_title_ar' => $request->first_title_ar ?? '',
                'first_title_kr' => $request->first_title_kr ?? '',
                'second_title_ar' => $request->second_title_ar ?? '',
                'second_title_kr' => $request->second_title_kr ?? '',
                'third_title_ar' => $request->third_title_ar ?? '',
                'third_title_kr' => $request->third_title_kr ?? '',
                'default_price_s' => $request->default_price_s ?? [],
                'default_price_p' => $request->default_price_p ?? [],
                'usd_to_aed_rate' => $request->usd_to_aed_rate ?? 3.6725,
                'usd_to_dinar_rate' => $request->usd_to_dinar_rate ?? 150.00,
                'contract_terms' => $request->contract_terms ?? null,
                'contract_terms_2' => $request->contract_terms_2 ?? null,
                'external_contract_terms' => $request->external_contract_terms ?? null,
                'external_contract_terms_2' => $request->external_contract_terms_2 ?? null,
                'contract_template' => $request->contract_template ?? 1,
                'contract_currency' => $request->contract_currency ?? 'usd',
                'primary_color' => $request->primary_color ?? '#c00',
            ]);
        } else {
            $updateData = [];
            if ($request->has('first_title_ar')) {
                $updateData['first_title_ar'] = $request->first_title_ar;
            }
            if ($request->has('first_title_kr')) {
                $updateData['first_title_kr'] = $request->first_title_kr;
            }
            if ($request->has('second_title_ar')) {
                $updateData['second_title_ar'] = $request->second_title_ar;
            }
            if ($request->has('second_title_kr')) {
                $updateData['second_title_kr'] = $request->second_title_kr;
            }
            if ($request->has('third_title_ar')) {
                $updateData['third_title_ar'] = $request->third_title_ar;
            }
            if ($request->has('third_title_kr')) {
                $updateData['third_title_kr'] = $request->third_title_kr;
            }
            if ($request->has('default_price_s') && (int) auth()->user()->type_id === 1) {
                $updateData['default_price_s'] = $request->default_price_s;
            }
            if ($request->has('default_price_p') && (int) auth()->user()->type_id === 1) {
                $updateData['default_price_p'] = $request->default_price_p;
            }
            if ($request->has('usd_to_aed_rate')) {
                $updateData['usd_to_aed_rate'] = $request->usd_to_aed_rate;
            }
            if ($request->has('usd_to_dinar_rate')) {
                $updateData['usd_to_dinar_rate'] = $request->usd_to_dinar_rate;
            }
            if ($request->has('contract_terms')) {
                $updateData['contract_terms'] = $request->contract_terms;
            }
            if ($request->has('contract_terms_2')) {
                $updateData['contract_terms_2'] = $request->contract_terms_2;
            }
            if ($request->has('external_contract_terms')) {
                $updateData['external_contract_terms'] = $request->external_contract_terms;
            }
            if ($request->has('external_contract_terms_2')) {
                $updateData['external_contract_terms_2'] = $request->external_contract_terms_2;
            }
            if ($request->has('contract_template')) {
                $updateData['contract_template'] = (int) $request->contract_template;
            }
            if ($request->has('contract_currency')) {
                $updateData['contract_currency'] = $request->contract_currency;
            }
            if ($request->has('primary_color')) {
                $updateData['primary_color'] = $request->primary_color ?: '#c00';
            }

            SystemConfig::ensureWhatsAppColumns();

            if ($request->has('wa_enabled')) {
                $updateData['wa_enabled'] = filter_var($request->input('wa_enabled'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('wa_tenant')) {
                $updateData['wa_tenant'] = $request->wa_tenant ? trim((string) $request->wa_tenant) : null;
            }
            if ($request->has('wa_base_url')) {
                $updateData['wa_base_url'] = $request->wa_base_url ? rtrim(trim((string) $request->wa_base_url), '/') : null;
            }
            if ($request->has('wa_created_by')) {
                $updateData['wa_created_by'] = $request->wa_created_by ? trim((string) $request->wa_created_by) : null;
            }
            if ($request->has('wa_notify_client_debt')) {
                $updateData['wa_notify_client_debt'] = filter_var($request->input('wa_notify_client_debt'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('wa_notify_payment_receipt')) {
                $updateData['wa_notify_payment_receipt'] = filter_var($request->input('wa_notify_payment_receipt'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('wa_notify_car_added')) {
                $updateData['wa_notify_car_added'] = filter_var($request->input('wa_notify_car_added'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->has('wa_msg_client_debt')) {
                $updateData['wa_msg_client_debt'] = $request->wa_msg_client_debt;
            }
            if ($request->has('wa_msg_payment_receipt')) {
                $updateData['wa_msg_payment_receipt'] = $request->wa_msg_payment_receipt;
            }
            if ($request->has('wa_msg_car_added')) {
                $updateData['wa_msg_car_added'] = $request->wa_msg_car_added;
            }
            if ($request->exists('car_expenses_wallet_user_id')) {
                $walletUserId = $request->input('car_expenses_wallet_user_id');
                $updateData['car_expenses_wallet_user_id'] = $walletUserId !== null && $walletUserId !== ''
                    ? (int) $walletUserId
                    : null;
            }

            $config->update($updateData);
        }

        $fresh = $config->fresh();
        $payload = $fresh->toArray();
        $payload['car_expenses_wallet'] = $this->resolveCarExpensesWallet(
            $payload['car_expenses_wallet_user_id'] ?? null
        );

        return Response::json([
            'message' => 'تم تحديث الإعدادات بنجاح',
            'config' => $payload,
        ], 200);
    }

    /**
     * بحث وباجينيشن لقاصات ترحيل مصاريف التسجيل (كل الأنواع).
     */
    public function searchCarExpensesWallets(Request $request)
    {
        if (auth()->user() && (int) auth()->user()->type_id === 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        $ownerId = Auth::user()?->owner_id;
        if (! $ownerId) {
            return Response::json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
            ]);
        }

        $q = trim((string) $request->input('q', ''));
        $typeLabels = $this->userTypeLabels();
        $matchingTypeNames = $this->typeNamesMatchingSearch($q, $typeLabels);

        $paginator = User::query()
            ->leftJoin('user_type', 'users.type_id', '=', 'user_type.id')
            ->where('users.owner_id', $ownerId)
            ->where(function ($query) {
                $query->whereNull('users.email')
                    ->orWhereNotIn('users.email', ['mainBox@account.com', 'main@account.com']);
            })
            ->whereHas('wallet')
            ->when($q !== '', function ($query) use ($q, $matchingTypeNames) {
                $query->where(function ($inner) use ($q, $matchingTypeNames) {
                    $inner->where('users.name', 'like', '%'.$q.'%')
                        ->orWhere('user_type.name', 'like', '%'.$q.'%');
                    if (ctype_digit($q)) {
                        $inner->orWhere('users.id', (int) $q);
                    }
                    if ($matchingTypeNames !== []) {
                        $inner->orWhereIn('user_type.name', $matchingTypeNames);
                    }
                });
            })
            ->orderBy('users.name')
            ->select([
                'users.id',
                'users.name',
                'users.type_id',
                'user_type.name as type_name',
            ])
            ->paginate(20);

        $paginator->getCollection()->transform(function ($user) use ($typeLabels) {
            $typeName = $user->type_name;
            $typeLabel = $typeName ? ($typeLabels[$typeName] ?? $typeName) : '—';

            return [
                'id' => (int) $user->id,
                'name' => $user->name,
                'type' => $typeName,
                'type_label' => $typeLabel,
                'label' => trim((string) $user->name).' — '.$typeLabel.' (#'.$user->id.')',
            ];
        });

        return Response::json($paginator);
    }

    /**
     * @return array{id:int,name:string,type:?string,type_label:string,label:string}|null
     */
    private function resolveCarExpensesWallet(mixed $userId): ?array
    {
        if (! $userId) {
            return null;
        }

        $ownerId = Auth::user()?->owner_id;
        if (! $ownerId) {
            return null;
        }

        $typeLabels = $this->userTypeLabels();

        $user = User::query()
            ->leftJoin('user_type', 'users.type_id', '=', 'user_type.id')
            ->where('users.owner_id', $ownerId)
            ->where('users.id', (int) $userId)
            ->select([
                'users.id',
                'users.name',
                'users.type_id',
                'user_type.name as type_name',
            ])
            ->first();

        if (! $user) {
            return null;
        }

        $typeName = $user->type_name;
        $typeLabel = $typeName ? ($typeLabels[$typeName] ?? $typeName) : '—';

        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'type' => $typeName,
            'type_label' => $typeLabel,
            'label' => trim((string) $user->name).' — '.$typeLabel.' (#'.$user->id.')',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function userTypeLabels(): array
    {
        return [
            'admin' => 'مدير النظام',
            'client' => 'زبون',
            'clientAnnual' => 'زبون سنوي',
            'account' => 'حساب / صندوق',
            'seles' => 'مبيعات',
            'selesKirkuk' => 'مبيعات كركوك',
            'car_expenses' => 'مصاريف سيارات',
            'car_contract_user' => 'مستخدم عقود',
            'car_contract' => 'إدارة عقود',
            'internal_sales_client' => 'زبون مبيعات داخلية',
            'shipping_company' => 'شركة شحن',
        ];
    }

    /**
     * @param  array<string, string>  $typeLabels
     * @return list<string>
     */
    private function typeNamesMatchingSearch(string $q, array $typeLabels): array
    {
        $q = mb_strtolower(trim($q));
        if ($q === '') {
            return [];
        }

        $names = [];
        foreach ($typeLabels as $name => $label) {
            if (
                str_contains(mb_strtolower((string) $label), $q)
                || str_contains(mb_strtolower((string) $name), $q)
            ) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * رفع شعار النظام وتخزين المسار في system_config.logo
     */
    public function uploadLogo(Request $request)
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = $this->firstOrCreateConfig();

        try {
            $relativePath = $this->storeSystemImage($request->file('logo'), 'logo');
        } catch (\Throwable $e) {
            Log::error('Logo upload store failed', ['error' => $e->getMessage()]);

            return Response::json(['error' => 'فشل حفظ ملف الشعار على السيرفر'], 500);
        }

        $oldLogo = $config->logo;
        if (! $this->persistMediaPath($config->id, 'logo', $relativePath)) {
            File::delete(public_path($relativePath));

            return Response::json([
                'error' => 'فشل حفظ مسار الشعار في قاعدة البيانات. تأكد من تشغيل migrate لعمود logo.',
            ], 500);
        }

        if ($oldLogo && $oldLogo !== $relativePath) {
            $this->deleteStoredMedia($oldLogo);
        }

        $fresh = SystemConfig::find($config->id);

        return Response::json([
            'message' => 'تم تحديث الشعار بنجاح',
            'config' => $fresh,
            'logo_url' => SystemConfig::resolveLogoUrl($relativePath),
        ], 200);
    }

    /**
     * حذف الشعار المخصص والرجوع للافتراضي
     */
    public function deleteLogo()
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $config = SystemConfig::first();
        if ($config && $config->logo) {
            $this->deleteStoredMedia($config->logo);
            $config->update(['logo' => null]);
        }

        return Response::json([
            'message' => 'تم حذف الشعار المخصص',
            'config' => $config?->fresh(),
            'logo_url' => SystemConfig::resolveLogoUrl(),
        ], 200);
    }

    /**
     * رفع خلفية صفحة تسجيل الدخول
     */
    public function uploadLoginBackground(Request $request)
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $validator = Validator::make($request->all(), [
            'login_background' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'error' => 'خطأ في التحقق من البيانات',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = $this->firstOrCreateConfig();

        try {
            $relativePath = $this->storeSystemImage($request->file('login_background'), 'login_bg');
        } catch (\Throwable $e) {
            Log::error('Login background upload store failed', ['error' => $e->getMessage()]);

            return Response::json(['error' => 'فشل حفظ ملف الخلفية على السيرفر'], 500);
        }

        $oldBg = $config->login_background;
        if (! $this->persistMediaPath($config->id, 'login_background', $relativePath)) {
            File::delete(public_path($relativePath));

            return Response::json([
                'error' => 'فشل حفظ مسار الخلفية في قاعدة البيانات. تأكد من تشغيل migrate.',
            ], 500);
        }

        if ($oldBg && $oldBg !== $relativePath) {
            $this->deleteStoredMedia($oldBg);
        }

        $fresh = SystemConfig::find($config->id);

        return Response::json([
            'message' => 'تم تحديث خلفية تسجيل الدخول بنجاح',
            'config' => $fresh,
            'login_background_url' => SystemConfig::resolveLoginBackgroundUrl($relativePath),
        ], 200);
    }

    /**
     * حذف خلفية تسجيل الدخول المخصصة
     */
    public function deleteLoginBackground()
    {
        if (auth()->user() && auth()->user()->type_id == 10) {
            return Response::json(['error' => 'غير مسموح الوصول'], 403);
        }

        SystemConfig::ensureMediaColumns();

        $config = SystemConfig::first();
        if ($config && $config->login_background) {
            $this->deleteStoredMedia($config->login_background);
            $config->update(['login_background' => null]);
        }

        return Response::json([
            'message' => 'تم حذف خلفية تسجيل الدخول',
            'config' => $config?->fresh(),
            'login_background_url' => null,
        ], 200);
    }

    /**
     * Serve stored system media (logo / login background) through Laravel.
     * Fixes hosts where /uploads/* is not publicly reachable.
     */
    public function serveSystemMedia(string $file)
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            abort(404);
        }

        $absolute = SystemConfig::resolveMediaAbsolutePath('img/system/'.$file)
            ?: SystemConfig::resolveMediaAbsolutePath('uploads/system/'.$file)
            ?: SystemConfig::resolveMediaAbsolutePath('storage/system/'.$file);

        if (! $absolute) {
            abort(404);
        }

        return response()->file($absolute, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    protected function firstOrCreateConfig(): SystemConfig
    {
        $config = SystemConfig::first();
        if ($config) {
            return $config;
        }

        return SystemConfig::create([
            'first_title_ar' => '',
            'first_title_kr' => '',
            'second_title_ar' => '',
            'second_title_kr' => '',
            'third_title_ar' => '',
            'third_title_kr' => '',
        ]);
    }

    /**
     * Persist media path via query builder (reliable on SQLite after ensureMediaColumns).
     */
    protected function persistMediaPath(int $id, string $column, ?string $path): bool
    {
        SystemConfig::ensureMediaColumns();

        try {
            DB::table('system_config')->where('id', $id)->update([$column => $path]);

            return true;
        } catch (\Throwable $e) {
            Log::error('persistMediaPath failed', [
                'column' => $column,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function deleteStoredMedia(?string $storedPath): void
    {
        $absolute = SystemConfig::resolveMediaAbsolutePath($storedPath);
        if ($absolute && File::exists($absolute)) {
            File::delete($absolute);
        }
    }

    public function databaseInsights(): \Illuminate\Http\JsonResponse
    {
        try {
            $driver = (string) DB::getDriverName();
            $path   = (string) config('database.connections.' . $driver . '.database', '');
            $dbSize = ($driver === 'sqlite' && File::exists($path)) ? (int) File::size($path) : null;

            $pageSize      = $driver === 'sqlite' ? (int) DB::selectOne('PRAGMA page_size')->page_size      : 0;
            $pageCount     = $driver === 'sqlite' ? (int) DB::selectOne('PRAGMA page_count')->page_count     : 0;
            $freelistCount = $driver === 'sqlite' ? (int) DB::selectOne('PRAGMA freelist_count')->freelist_count : 0;
            $usedBytes     = $pageSize > 0 ? ($pageCount - $freelistCount) * $pageSize : null;
            $freeBytes     = $pageSize > 0 ? $freelistCount * $pageSize : null;

            $sizeMap = [];
            if ($driver === 'sqlite') {
                try {
                    foreach (DB::select('SELECT name, SUM(pgsize) AS sz FROM dbstat GROUP BY name') as $r) {
                        $sizeMap[(string) $r->name] = (int) $r->sz;
                    }
                } catch (\Throwable) {}
            }

            $tables = $driver === 'sqlite'
                ? DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
                : DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');

            $items = [];
            foreach ($tables as $t) {
                $name  = (string) ($t->name ?? '');
                $rows  = 0;
                try { $rows = (int) DB::table($name)->count(); } catch (\Throwable) {}
                $sz    = $sizeMap[$name] ?? null;
                $items[] = [
                    'name'        => $name,
                    'rows'        => $rows,
                    'size_bytes'  => $sz,
                    'percent'     => $dbSize && $sz ? round($sz / $dbSize * 100, 2) : null,
                ];
            }
            usort($items, fn ($a, $b) => ($b['size_bytes'] ?? -1) <=> ($a['size_bytes'] ?? -1));

            return response()->json([
                'driver'      => $driver,
                'db_size'     => $dbSize,
                'used_bytes'  => $usedBytes,
                'free_bytes'  => $freeBytes,
                'tables'      => $items,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function vacuumDatabase(): \Illuminate\Http\JsonResponse
    {
        try {
            $driver = (string) DB::getDriverName();
            if ($driver !== 'sqlite') {
                return response()->json(['message' => "VACUUM غير مدعوم لـ {$driver}.", 'skipped' => true]);
            }
            $path   = (string) config('database.connections.sqlite.database');
            $before = File::exists($path) ? (int) File::size($path) : null;
            DB::statement('VACUUM');
            $after  = File::exists($path) ? (int) File::size($path) : null;
            $saved  = ($before !== null && $after !== null) ? max(0, $before - $after) : null;
            return response()->json(['message' => 'تم تنفيذ VACUUM بنجاح.', 'before' => $before, 'after' => $after, 'saved' => $saved]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store under public/img/system (same public area as existing /img/logo.*).
     */
    protected function storeSystemImage($file, string $prefix): string
    {
        $dir = public_path('img/system');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $prefix.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $file->move($dir, $filename);

        $relativePath = 'img/system/'.$filename;
        if (! is_file(public_path($relativePath))) {
            throw new \RuntimeException('File missing after move: '.$relativePath);
        }

        return $relativePath;
    }
}
