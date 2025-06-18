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

class VehiclesReportSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['vehicles_report'] ?? []);
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->registration_number ?? '',
            $vehicle->brand ?? '',
            $vehicle->model ?? '',
            $vehicle->vehicleType->name ?? '',
            $vehicle->year ?? '',
            $vehicle->status ?? '',
            $vehicle->maintenance_operations_count ?? 0,
            number_format($vehicle->maintenance_operations_sum_total_cost ?? 0, 0, ',', ' ')
        ];
    }

    public function headings(): array
    {
        return [
            'Immatriculation',
            'Marque',
            'Modèle',
            'Type',
            'Année',
            'Statut',
            'Nb opérations',
            'Coût total (FCFA)'
        ];
    }

    public function title(): string
    {
        return 'Rapport véhicules';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E1D5E7']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ],
            'A:H' => [
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]
        ];
    }
}