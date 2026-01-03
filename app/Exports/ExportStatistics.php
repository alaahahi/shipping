<?php

namespace App\Exports;

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

class ExportStatistics implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected $statistics;
    protected $year;
    protected $years;

    public function __construct($statistics, $year = null, $years = [])
    {
        $this->statistics = $statistics;
        $this->year = $year;
        $this->years = is_array($years) ? $years : [];
    }

    public function collection()
    {
        $collection = new Collection();
        
        // الإحصائيات الرئيسية
        $collection->push([
            'نوع الإحصائية' => 'إجمالي السيارات',
            'القيمة' => $this->statistics['cars_count'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'مجموع المبيعات',
            'القيمة' => $this->statistics['total_sales'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'مجموع المشتريات',
            'القيمة' => $this->statistics['total_purchases'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'الفرق (مبيعات - مشتريات)',
            'القيمة' => $this->statistics['sales_purchase_difference'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'دين التجار',
            'القيمة' => $this->statistics['traders_debt'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'مجموع الجمرك',
            'القيمة' => $this->statistics['total_customs'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'الفائدة من فرق سعر الصرف',
            'القيمة' => $this->statistics['exchange_profit'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'صافي الربح',
            'القيمة' => $this->statistics['net_profit'] ?? 0,
        ]);
        $collection->push([
            'نوع الإحصائية' => 'صافي الحولات',
            'القيمة' => $this->statistics['net_transfers'] ?? 0,
        ]);
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'نوع الإحصائية',
            'القيمة',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
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
                $sheet->mergeCells('A1:B1');
                $sheet->setCellValue('A1', 'تقرير الإحصائيات العامة');
                
                $titleStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 18,
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
                if (!empty($this->years)) {
                    $yearsText = 'السنوات: ' . implode(', ', $this->years);
                } elseif ($this->year) {
                    $yearsText = 'السنة: ' . $this->year;
                } else {
                    $yearsText = 'جميع السنوات';
                }
                $sheet->mergeCells('A2:B2');
                $sheet->setCellValue('A2', $yearsText);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 12, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(20);
                
                // تنسيق رأس الجدول (الآن في السطر 3)
                $headerRange = 'A3:B3';
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
                $dataRange = 'A4:B' . $lastRow;
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
                
                // تنسيق أرقام القيم
                $sheet->getStyle('B4:B' . $lastRow)->getNumberFormat()
                    ->setFormatCode('#,##0');
                
                // تنسيق صفوف البيانات (ألوان متناوبة)
                for ($row = 4; $row <= $lastRow; $row++) {
                    $fillColor = ($row % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
                    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $fillColor],
                        ],
                    ]);
                }
                
                // إضافة صف فارغ ثم الأرباح الشهرية
                $monthlyRow = $lastRow + 2;
                $sheet->setCellValue('A' . $monthlyRow, 'الأرباح الشهرية');
                $sheet->mergeCells('A' . $monthlyRow . ':B' . $monthlyRow);
                $sheet->getStyle('A' . $monthlyRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E78']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension($monthlyRow)->setRowHeight(25);
                
                // رأس جدول الأرباح الشهرية
                $monthlyHeaderRow = $monthlyRow + 1;
                $sheet->setCellValue('A' . $monthlyHeaderRow, 'الشهر');
                $sheet->setCellValue('B' . $monthlyHeaderRow, 'الربح');
                $sheet->getStyle('A' . $monthlyHeaderRow . ':B' . $monthlyHeaderRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '70AD47'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                    ],
                ]);
                $sheet->getRowDimension($monthlyHeaderRow)->setRowHeight(25);
                
                // بيانات الأرباح الشهرية
                if (isset($this->statistics['month_labels']) && isset($this->statistics['monthly_profits'])) {
                    $monthLabels = $this->statistics['month_labels'];
                    $monthlyProfits = $this->statistics['monthly_profits'];
                    $dataRow = $monthlyHeaderRow + 1;
                    
                    foreach ($monthLabels as $index => $label) {
                        $sheet->setCellValue('A' . $dataRow, $label);
                        $sheet->setCellValue('B' . $dataRow, $monthlyProfits[$index] ?? 0);
                        $fillColor = ($dataRow % 2 == 0) ? 'E2EFDA' : 'FFFFFF';
                        $sheet->getStyle('A' . $dataRow . ':B' . $dataRow)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $fillColor],
                            ],
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                            ],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        ]);
                        $sheet->getStyle('B' . $dataRow)->getNumberFormat()->setFormatCode('#,##0');
                        $dataRow++;
                    }
                    
                    // صف المجموع
                    $totalRow = $dataRow;
                    $sheet->setCellValue('A' . $totalRow, 'المجموع');
                    $sheet->setCellValue('B' . $totalRow, array_sum($monthlyProfits));
                    $sheet->getStyle('A' . $totalRow . ':B' . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'C5E0B4'],
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                    $sheet->getStyle('B' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
                }
            },
        ];
    }

    public function title(): string
    {
        return 'الإحصائيات العامة';
    }
}

