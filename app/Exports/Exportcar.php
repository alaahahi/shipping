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
    
    public function __construct($from,$to)
    {
        $this->from = $from;
        $this->to=$to;
    }


    public function collection()
    {
        
        // Fetch data from the database using the Car model
        if($this->from && $this->to){
            $cars = Car::whereBetween('date', [$this->from, $this->to])->
            select([
                'car_type',
                'vin',
                'car_number',
                'car_color',
                'dinar_s',
            ])->get();
        } 
     

        // Transform the fetched data into a collection
        $collection = new Collection();

        $seqNo = 1; // Initialize a sequence number


        foreach ($cars as $car) {
            $car['seqNo'] = $seqNo; // Add the sequence number to the car
            $seqNo++; // Increment the sequence number
            $collection->push($car->toArray());
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'السيارة',
            'الشانصى',
            'رقم كاتي',
            'اللون',
            'كمرك بالدينار',
            'تسلسل'
        ];
    }

    private function formatResults($value)
    {
        switch ($value) {
            case 0:
                return 'غير مدفوع';
            case 1:
            case 2:
                return 'مدفوع';
            default:
                return '';
        }
    }
}