<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteDrivingAuthorizationRequest;
use App\Http\Requests\UpdateDrivingAuthorizationRequest;
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

    public function show($id)
    {
        try {
            $doc = $this->service->find((int) $id, (int) Auth::user()->owner_id);
        } catch (\RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'authorized') ? 403 : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return (new DrivingAuthorizationResource($doc))->withRendered();
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

    public function update(UpdateDrivingAuthorizationRequest $request)
    {
        $data = $request->validated();

        try {
            $doc = $this->service->update(
                (int) $data['id'],
                (int) Auth::user()->owner_id,
                $data
            );
        } catch (\RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'authorized') ? 403 : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return (new DrivingAuthorizationResource($doc))
            ->withRendered()
            ->additional(['message' => 'تم تعديل تخويل القيادة بنجاح']);
    }

    public function destroy(DeleteDrivingAuthorizationRequest $request)
    {
        try {
            $doc = $this->service->delete(
                (int) $request->validated()['id'],
                (int) Auth::user()->owner_id
            );
        } catch (\RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'authorized') ? 403 : 404;

            return response()->json(['message' => $e->getMessage()], $status);
        }

        return response()->json([
            'message' => 'تم حذف تخويل القيادة بنجاح',
            'id' => $doc->id,
        ]);
    }
}
