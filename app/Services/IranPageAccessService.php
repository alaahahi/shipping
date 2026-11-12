<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IranPageAccessService
{
    public const SESSION_KEY = 'iran_page_unlocked';

    public const TOKEN_SESSION_KEY = 'iran_page_token_ok';

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

    /**
     * Default owner for token-guest sessions (no logged-in user).
     */
    public function ownerId(Request $request): int
    {
        $user = $request->user();
        if ($user && $user->owner_id !== null) {
            return (int) $user->owner_id;
        }

        return (int) config('iran.owner_id', 1);
    }

    /**
     * Persist a *valid* token. An explicitly wrong token clears memory and fails.
     */
    public function rememberValidToken(Request $request): bool
    {
        $provided = $this->tokenFromRequest($request);

        if ($provided !== null && $provided !== '') {
            if (! $this->tokenMatches($request)) {
                $this->forgetTokenAccess($request);

                return false;
            }

            $request->session()->put(self::TOKEN_SESSION_KEY, true);

            return true;
        }

        return $this->hasRememberedToken($request);
    }

    public function hasRememberedToken(Request $request): bool
    {
        return (bool) $request->session()->get(self::TOKEN_SESSION_KEY, false);
    }

    public function hasValidTokenAccess(Request $request): bool
    {
        return $this->rememberValidToken($request);
    }

    public function forgetTokenAccess(Request $request): void
    {
        $request->session()->forget(self::TOKEN_SESSION_KEY);
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Page/API entry:
     * - Wrong token in request → always denied (even if logged in / previously remembered).
     * - Valid token → allowed (guest or logged-in).
     * - No token → logged-in user OR previously remembered valid token.
     */
    public function canEnter(Request $request): bool
    {
        $provided = $this->tokenFromRequest($request);

        if ($provided !== null && $provided !== '') {
            if (! $this->tokenMatches($request)) {
                $this->forgetTokenAccess($request);

                return false;
            }

            $request->session()->put(self::TOKEN_SESSION_KEY, true);

            return true;
        }

        if ($request->user()) {
            return true;
        }

        return $this->hasRememberedToken($request);
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

        $query = $request->query('token');
        if (is_string($query) && $query !== '') {
            return $query;
        }

        return null;
    }

    public function pageProps(Request $request): array
    {
        // Persist token from ?token= / header into the session for later API calls.
        // Wrong token clears access — do not keep a stale "remembered" unlock.
        $this->rememberValidToken($request);

        $user = $request->user();
        $unlocked = $this->isUnlocked($request);
        $isAdmin = $this->isAdmin($user);

        return [
            'iran_unlocked' => $unlocked,
            'iran_can_delete' => $isAdmin,
            'iran_is_admin' => $isAdmin,
            'iran_token_guest' => $user === null && $this->hasRememberedToken($request),
        ];
    }

    protected function adminTypeId(): ?int
    {
        if (! Schema::hasTable('user_type')) {
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
