<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SparePartsSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        if (isset($this->data['spare_parts_report'])) {
            foreach ($this->data['spare_parts_report'] as $part) {
                $rows[] = [
                    $part->code,
                    $part->name,
                    $part->category,
                    $part->unit,
                    $part->quantity_in_stock,
                    $part->total_used ?? 0,
                    number_format($part->total_value ?? 0, 0, ',', ' '),
                ];
            }
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Code',
            'Nom',
            'Catégorie',
            'Unité',
            'Stock actuel',
            'Quantité utilisée',
            'Valeur totale (FCFA)'
        ];
    }

    public function title(): string
    {
        return 'Pièces détachées';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
