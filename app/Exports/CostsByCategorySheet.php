<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CostsByCategorySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();

        if (isset($this->data['costs_by_category'])) {
            foreach ($this->data['costs_by_category'] as $category) {
                $rows->push([
                    ucfirst($category->category),
                    $category->operations_count,
                    number_format($category->total_cost, 0, ',', ' '),
                    number_format($category->average_cost ?? 0, 0, ',', ' ')
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Catégorie',
            'Nombre d\'opérations',
            'Coût total (FCFA)',
            'Coût moyen (FCFA)'
        ];
    }

    public function title(): string
    {
        return 'Coûts par catégorie';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFF2CC']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
            'A:D' => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]
        ];
    }
}