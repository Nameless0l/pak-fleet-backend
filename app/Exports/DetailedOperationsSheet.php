<?php
namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailedOperationsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['operations'] ?? []);
    }

    public function map($operation): array
    {
        return [
            $operation->operation_date ?? '',
            $operation->vehicle->registration_number ?? '',
            $operation->vehicle->brand ?? '' . ' ' . ($operation->vehicle->model ?? ''),
            $operation->vehicle->vehicleType->name ?? '',
            $operation->maintenanceType->name ?? '',
            $operation->maintenanceType->category ?? '',
            $operation->technician->name ?? '',
            $operation->description ?? '',
            number_format($operation->labor_cost ?? 0, 0, ',', ' '),
            number_format($operation->parts_cost ?? 0, 0, ',', ' '),
            number_format($operation->total_cost ?? 0, 0, ',', ' '),
            $operation->status ?? ''
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Immatriculation',
            'Véhicule',
            'Type de véhicule',
            'Type de maintenance',
            'Catégorie',
            'Technicien',
            'Description',
            'Coût main d\'œuvre (FCFA)',
            'Coût pièces (FCFA)',
            'Coût total (FCFA)',
            'Statut'
        ];
    }

    public function title(): string
    {
        return 'Opérations détaillées';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'D5E8D4']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
            'A:L' => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }
}