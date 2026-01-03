<?php

namespace App\Exports;

use App\Models\Transfers;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportTransfers implements FromCollection, WithHeadings
{
    protected $from;
    protected $to;
    protected $years;
    protected $month;

    public function __construct($from = null, $to = null, $years = [], $month = null)
    {
        $this->from = $from ? date('Y-m-d', strtotime($from)) : null;
        $this->to = $to ? date('Y-m-d', strtotime($to)) : null;
        $this->years = is_array($years) ? $years : [];
        $this->month = $month;
    }

    public function collection()
    {
        $query = Transfers::query();

        // فلترة حسب التاريخ (من - إلى)
        if ($this->from && $this->to) {
            $query->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
        } elseif ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        // فلترة حسب السنوات المتعددة
        if (count($this->years) > 0) {
            $query->where(function($q) {
                foreach ($this->years as $y) {
                    $q->orWhereYear('created_at', $y);
                }
            });
        }

        // فلترة حسب الشهر
        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        $collection = new Collection();
        $seqNo = 1;

        foreach ($transfers as $transfer) {
            $transferData = [
                'seqNo' => $seqNo++,
                'no' => $transfer->no ?? '',
                'amount' => $transfer->amount ?? 0,
                'fee' => $transfer->fee ?? 0,
                'net' => ($transfer->amount ?? 0) - ($transfer->fee ?? 0),
                'status' => $transfer->stauts ?? $transfer->status ?? '',
                'sender_note' => $transfer->sender_note ?? '',
                'receiver_note' => $transfer->receiver_note ?? '',
                'currency' => $transfer->currency ?? '',
                'created_at' => $transfer->created_at ? date('Y-m-d H:i:s', strtotime($transfer->created_at)) : '',
            ];

            $collection->push($transferData);
        }

        return $collection;
    }

    public function headings(): array
    {
        return [
            'تسلسل',
            'الرقم',
            'المبلغ',
            'الرسوم',
            'الصافي',
            'الحالة',
            'ملاحظة المرسل',
            'ملاحظة المستقبل',
            'العملة',
            'التاريخ',
        ];
    }
}

