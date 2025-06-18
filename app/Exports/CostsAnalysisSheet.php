<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CostsAnalysisSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();

        // Ajouter ici l'analyse détaillée des coûts selon vos besoins
        $rows->push(['Analyse détaillée des coûts', '']);
        $rows->push(['À implémenter selon les besoins spécifiques', '']);

        return $rows;
    }

    public function headings(): array
    {
        return ['Analyse', 'Valeur'];
    }

    public function title(): string
    {
        return 'Analyse des coûts';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]
            ]
        ];
    }
}