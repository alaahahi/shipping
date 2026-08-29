<?php

namespace App\Http\Resources;

use App\Services\DrivingAuthorizationService;
use Illuminate\Http\Resources\Json\JsonResource;

class DrivingAuthorizationResource extends JsonResource
{
    /**
     * نص التخويل النهائي يُحسب فقط عند طلب سجل واحد (تجنّب استعلام لكل صف في القائمة).
     */
    protected $withRendered = false;

    public function withRendered(bool $value = true): self
    {
        $this->withRendered = $value;

        return $this;
    }

    public function toArray($request)
    {
        $data = [
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
            'print_url' => '/makeDrivingDocumentPdf?doc_id='.$this->id,
        ];

        if ($this->withRendered) {
            $data['rendered_note'] = app(DrivingAuthorizationService::class)
                ->render($this->note, $this->resource);
        }

        return $data;
    }
}
