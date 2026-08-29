<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecodeVinRequest;
use App\Services\VinDecoderService;
use Illuminate\Http\JsonResponse;

class VinLookupController extends Controller
{
    public function __construct(private readonly VinDecoderService $vinDecoder)
    {
    }

    public function decode(DecodeVinRequest $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->vinDecoder->decode($request->vin()),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }
    }
}
