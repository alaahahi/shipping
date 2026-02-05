<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlineUsersController extends Controller
{
    /**
     * عدد الدقائق لاعتبار المستخدم متصل
     */
    const ONLINE_MINUTES = 2;

    /**
     * جلب قائمة المستخدمين المتصلين (نفس owner_id)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['online_users' => []]);
        }

        $threshold = now()->subMinutes(self::ONLINE_MINUTES);

        $others = User::where('owner_id', $user->owner_id)
            ->where('id', '!=', $user->id)
            ->whereNotNull('last_activity')
            ->where('last_activity', '>=', $threshold)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'initials' => $this->getInitials($u->name),
                    'is_me' => false,
                ];
            });

        // إضافة المستخدم الحالي أولاً (دائماً يظهر)
        $currentUser = [
            'id' => $user->id,
            'name' => 'أنت (' . $user->name . ')',
            'initials' => $this->getInitials($user->name),
            'is_me' => true,
        ];

        $onlineUsers = collect([$currentUser])->merge($others->values())->values()->all();

        return response()->json(['online_users' => $onlineUsers]);
    }

    /**
     * استخراج حرفين من الاسم (أول حرف من كل كلمة أو أول حرفين)
     */
    private function getInitials(string $name): string
    {
        $name = trim($name);
        if (empty($name)) {
            return '?';
        }
        $parts = preg_split('/\s+/', $name, 2);
        if (count($parts) >= 2) {
            return mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
        }
        return mb_substr($name, 0, 2);
    }
}
