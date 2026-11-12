<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnlockIranPageRequest;
use App\Services\IranPageAccessService;
use Illuminate\Http\JsonResponse;

class IranPageAccessController extends Controller
{
    public function __construct(protected IranPageAccessService $access)
    {
    }

    public function unlock(UnlockIranPageRequest $request): JsonResponse
    {
        $this->access->unlock($request);

        return response()->json([
            'unlocked' => true,
            'can_delete' => $this->access->isAdmin($request->user()),
            'is_admin' => $this->access->isAdmin($request->user()),
        ]);
    }
}
