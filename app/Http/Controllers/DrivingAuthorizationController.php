<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDrivingAuthorizationTextRequest;
use App\Http\Resources\DrivingAuthorizationResource;
use App\Services\DrivingAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DrivingAuthorizationController extends Controller
{
    public function __construct(private readonly DrivingAuthorizationService $service)
    {
    }

    public function index()
    {
        return Inertia::render('DrivingAuthorizations/Index');
    }

    public function list(Request $request)
    {
        $paginator = $this->service->paginate((int) Auth::user()->owner_id, [
            'q' => $request->query('q'),
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'per_page' => $request->query('per_page'),
        ]);

        return DrivingAuthorizationResource::collection($paginator);
    }

    public function template()
    {
        return response()->json([
            'text' => $this->service->template(),
            'default' => DrivingAuthorizationService::DEFAULT_TEXT,
            'placeholders' => DrivingAuthorizationService::PLACEHOLDERS,
        ]);
    }

    public function updateTemplate(UpdateDrivingAuthorizationTextRequest $request)
    {
        $text = $this->service->saveTemplate($request->validated('driving_authorization_text'));

        return response()->json([
            'message' => 'تم حفظ نص تخويل القيادة بنجاح',
            'text' => $text,
        ]);
    }
}
