<?php

namespace App\Http\Resources;

use App\Services\DrivingAuthorizationService;
use Illuminate\Http\Resources\Json\JsonResource;

class DrivingAuthorizationResource extends JsonResource
{
    public function toArray($request)
    {
        $service = app(DrivingAuthorizationService::class);

        return [
            'id' => (int) $this->id,
            'client_id' => $this->client_id,
            'name' => $this->name,
            'car_type' => $this->car_type,
            'car_number' => $this->car_number,
            'vin' => $this->vin,
            'year' => $this->year,
            'color' => $this->color,
            'created' => $this->created,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'note' => $this->note,
            'rendered_note' => $service->render($this->note, $this->resource),
            'print_url' => '/makeDrivingDocumentPdf?doc_id='.$this->id,
        ];
    }
}
