<?php

namespace App\Exports;

use App\Models\TripCar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TripCarsExport implements FromCollection, WithHeadings
{
    protected $tripCompanyId;

    public function __construct($tripCompanyId)
    {
        $this->tripCompanyId = $tripCompanyId;
    }

    public function collection()
    {
        $tripCars = TripCar::where('trip_company_id', $this->tripCompanyId)
            ->orderBy('id', 'asc')
            ->get();

        $collection = new Collection();
        $seqNo = 1;

        foreach ($tripCars as $tripCar) {
            $carData = [
                'S.NO' => $seqNo++,
                'WEIGHT' => $tripCar->weight ?? '',
                'SHIPPER' => $tripCar->shipper_name ?? '',
                'DESCRIPTION' => $tripCar->description ?? '',
                'CHASSIS NO' => $tripCar->chassis_no ?? '',
                'CONSIGNEE' => $tripCar->consignee_name ?? '',
                'CODE' => $tripCar->code ?? '',
            ];
            $collection->push($carData);
        }

        return $collection;
    }

    public function headings(): array
    {
        return [
            'S.NO',
            'WEIGHT',
            'SHIPPER',
            'DESCRIPTION',
            'CHASSIS NO',
            'CONSIGNEE',
            'CODE',
        ];
    }
}
