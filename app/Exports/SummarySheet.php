<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;



class SummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();

        // Informations générales
        $rows->push(['RAPPORT DE MAINTENANCE - RÉSUMÉ ANNUEL', '']);
        $rows->push(['Année', $this->data['year']]);
        $rows->push(['Généré le', $this->data['generated_at']]);
        $rows->push(['', '']);

        // Statistiques générales
        if (isset($this->data['stats'])) {
            $stats = $this->data['stats'];
            $rows->push(['STATISTIQUES GÉNÉRALES', '']);
            $rows->push(['Total véhicules', $stats['total_vehicles'] ?? 0]);
            $rows->push(['Véhicules actifs', $stats['active_vehicles'] ?? 0]);
            $rows->push(['Total opérations', $stats['total_operations'] ?? 0]);
            $rows->push(['Coût total', number_format($stats['total_cost'] ?? 0, 0, ',', ' ') . ' FCFA']);
            $rows->push(['Coût moyen par opération', number_format($stats['average_cost_per_operation'] ?? 0, 0, ',', ' ') . ' FCFA']);
            $rows->push(['', '']);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Indicateur', 'Valeur'];
    }

    public function title(): string
    {
        return 'Résumé';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '366092']],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
            ],
            'A:B' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }
}