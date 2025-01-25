<?php

namespace App\Exports;

use App\Models\Car;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportCar implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;
    protected $user_id;
    protected $owner_id;

    public function __construct($from, $to, $user_id, $owner_id)
    {
        $this->from = $from ? date('Y-m-d', strtotime($from)) : null;
        $this->to = $to ? date('Y-m-d', strtotime($to)) : null;
        $this->owner_id = intval($owner_id);
        $this->user_id = intval($user_id);
    }

    public function collection()
    {
        $query = Car::where('owner_id', $this->owner_id);

        if ($this->from && $this->to) {
            $query->whereBetween('date', [$this->from, $this->to]);
        }

        if ($this->user_id) {
            $query->where('client_id', $this->user_id);
        }

        $cars = $query->select([
            'owner_id',
            'profit',
            'total',
            'total_s',
            'car_type',
            'vin',
            'car_number',
            'car_color',
            'year',
            'dinar_s',
            'date'
        ])->get();

        $collection = new Collection();
        $seqNo = 1;

        foreach ($cars as $car) {
            $carArray = $car->toArray();
            $carArray['seqNo'] = $seqNo++;
            $collection->push($carArray);
        }

        return $collection;
    }

    public function headings(): array
    {
        return [
            'المالك',
            'الربح',
            'الإجمالي',
            'الإجمالي (المبيعات)',
            'نوع السيارة',
            'رقم الهيكل',
            'رقم السيارة',
            'لون السيارة',
            'سنة الصنع',
            'الدينار',
            'التاريخ',
            'تسلسل'
        ];
    }
}
