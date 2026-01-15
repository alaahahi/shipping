<?php

namespace App\Exports;

use App\Models\Transactions;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\DB;

class ExportPayments implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected $accounting;
    protected $owner_id;
    protected $year;
    protected $years;
    protected $month;

    public function __construct($accounting, $owner_id, $year = null, $years = [], $month = null)
    {
        $this->accounting = $accounting;
        $this->accounting->loadAccounts($owner_id);
        $this->owner_id = $owner_id;
        $this->year = $year;
        $this->years = is_array($years) ? $years : [];
        $this->month = $month;
    }

    public function collection()
    {
        // الحصول على client IDs
        $clientIds = User::where('type_id', $this->accounting->userClient())
            ->where('owner_id', $this->owner_id)
            ->pluck('id');
        
        // الحصول على wallet IDs
        $walletIds = Wallet::whereIn('user_id', $clientIds)->pluck('id');
        
        if ($walletIds->count() == 0) {
            return new Collection();
        }
        
        // استعلام الدفعات
        $paymentsQuery = Transactions::whereIn('wallet_id', $walletIds)
            ->where('type', 'out')
            ->where('is_pay', 1)
            ->where('amount', '<', 0)
            ->where('currency', '$')
            ->with('wallet.user');
        
        // فلترة حسب السنوات
        if (count($this->years) > 0) {
            $paymentsQuery->where(function($q) {
                foreach ($this->years as $y) {
                    $q->orWhereYear('created', $y);
                }
            });
        } elseif ($this->year) {
            $paymentsQuery->whereYear('created', $this->year);
        }
        
        // فلترة حسب الشهر
        if ($this->month) {
            $paymentsQuery->whereMonth('created', $this->month);
        }
        
        $payments = $paymentsQuery->orderBy('created', 'desc')->get();
        
        $collection = new Collection();
        $seqNo = 1;
        $totalAmount = 0;
        
        foreach ($payments as $payment) {
            $user = $payment->wallet->user ?? null;
            $amount = abs($payment->amount);
            $totalAmount += $amount;
            
            $paymentData = [
                'seqNo' => $seqNo++,
                'trader_name' => $user->name ?? 'N/A',
                'trader_phone' => $user->phone ?? 'N/A',
                'amount' => $amount,
                'description' => $payment->description ?? '',
                'created_at' => $payment->created ? date('Y-m-d H:i:s', strtotime($payment->created)) : ($payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : ''),
            ];
            
            $collection->push($paymentData);
        }
        
        // إضافة صف المجموع
        if ($collection->count() > 0) {
            $collection->push([
                'seqNo' => '',
                'trader_name' => 'المجموع',
                'trader_phone' => '',
                'amount' => $totalAmount,
                'description' => '',
                'created_at' => '',
            ]);
        }
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'تسلسل',
            'اسم التاجر',
            'هاتف التاجر',
            'المبلغ',
            'الوصف',
            'تاريخ الدفعة',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 20,
            'D' => 20,
            'E' => 40,
            'F' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // عنوان رئيسي
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'تصدير دفعات التجار');
                
                $titleStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E78'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                $sheet->getStyle('A1')->applyFromArray($titleStyle);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // معلومات الفلترة
                $filterText = '';
                if (!empty($this->years)) {
                    $filterText = 'السنوات: ' . implode(', ', $this->years);
                } elseif ($this->year) {
                    $filterText = 'السنة: ' . $this->year;
                } else {
                    $filterText = 'جميع السنوات';
                }
                if ($this->month) {
                    $monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                    $filterText .= ' - الشهر: ' . ($monthNames[$this->month] ?? $this->month);
                }
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', $filterText);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 11, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                // تنسيق رأس الجدول
                $headerRange = 'A3:F3';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(25);
                
                // تنسيق البيانات
                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A4:F' . ($lastRow - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);
                
                // تنسيق أرقام المبالغ
                $sheet->getStyle('D4:D' . ($lastRow - 1))->getNumberFormat()
                    ->setFormatCode('#,##0');
                
                // تنسيق صفوف البيانات (ألوان متناوبة)
                for ($row = 4; $row < $lastRow; $row++) {
                    $fillColor = ($row % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $fillColor],
                        ],
                    ]);
                }
                
                // تنسيق صف المجموع
                if ($lastRow > 4) {
                    $totalRow = $lastRow;
                    $sheet->getStyle('A' . $totalRow . ':F' . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'C5E0B4'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        ],
                    ]);
                    $sheet->getStyle('D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                }
            },
        ];
    }

    public function title(): string
    {
        return 'دفعات التجار';
    }
}

