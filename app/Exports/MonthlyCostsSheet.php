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

class MonthlyCostsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();

        if (isset($this->data['monthly_costs'])) {
            foreach ($this->data['monthly_costs'] as $month) {
                $rows->push([
                    $month['month'],
                    $month['operations_count'],
                    number_format($month['total_cost'], 0, ',', ' '),
                    number_format($month['labor_cost'] ?? 0, 0, ',', ' '),
                    number_format($month['parts_cost'] ?? 0, 0, ',', ' ')
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Mois',
            'Nombre d\'opérations',
            'Coût total (FCFA)',
            'Coût main d\'œuvre (FCFA)',
            'Coût pièces (FCFA)'
        ];
    }

    public function title(): string
    {
        return 'Coûts mensuels';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E2EFDA']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
            'A:E' => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]
        ];
    }
}