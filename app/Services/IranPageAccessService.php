<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IranPageAccessService
{
    public const SESSION_KEY = 'iran_page_unlocked';

    public function isAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $adminTypeId = $this->adminTypeId();
        if ($adminTypeId !== null) {
            return (int) $user->type_id === (int) $adminTypeId;
        }

        // Same fallback as driving-authorization delete.
        return (int) ($user->type_id ?? 0) === 1;
    }

    public function isUnlocked(Request $request): bool
    {
        if ($this->isAdmin($request->user())) {
            return true;
        }

        return (bool) $request->session()->get(self::SESSION_KEY, false);
    }

    public function pinMatches($pin): bool
    {
        $expected = (string) config('iran.page_pin');
        $given = (string) $pin;

        if ($expected === '' || $given === '') {
            return false;
        }

        return hash_equals(
            hash('sha256', $expected),
            hash('sha256', $given)
        );
    }

    public function unlock(Request $request): void
    {
        $request->session()->put(self::SESSION_KEY, true);
    }

    public function pageToken(): string
    {
        return (string) config('iran.page_token');
    }

    public function tokenMatches(Request $request): bool
    {
        $expected = $this->pageToken();
        if ($expected === '') {
            return false;
        }

        $provided = $this->tokenFromRequest($request);
        if ($provided === null || $provided === '') {
            return false;
        }

        return hash_equals($expected, $provided);
    }

    public function tokenFromRequest(Request $request): ?string
    {
        $header = $request->header('X-Iran-Token');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        $auth = $request->header('Authorization');
        if (is_string($auth) && strncmp($auth, 'Bearer ', 7) === 0) {
            return substr($auth, 7);
        }

        return null;
    }

    public function pageProps(Request $request): array
    {
        $user = $request->user();
        $unlocked = $this->isUnlocked($request);
        $isAdmin = $this->isAdmin($user);

        return [
            'iran_unlocked' => $unlocked,
            'iran_can_delete' => $isAdmin,
            'iran_is_admin' => $isAdmin,
        ];
    }

    protected function adminTypeId(): ?int
    {
        if (!Schema::hasTable('user_type')) {
            return null;
        }

        try {
            $id = UserType::where('name', 'admin')->value('id');
        } catch (\Throwable $e) {
            return null;
        }

        return $id !== null ? (int) $id : null;
    }
}
