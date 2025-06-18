<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\WithMultipleSheets;


class MaintenanceReportExport implements WithMultipleSheets
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        switch ($this->data['type']) {
            case 'summary':
                $sheets[] = new SummarySheet($this->data);
                if (isset($this->data['monthly_costs'])) {
                    $sheets[] = new MonthlyCostsSheet($this->data);
                }
                if (isset($this->data['costs_by_category'])) {
                    $sheets[] = new CostsByCategorySheet($this->data);
                }
                break;

            case 'detailed':
                $sheets[] = new DetailedOperationsSheet($this->data);
                break;

            case 'costs':
                $sheets[] = new CostsAnalysisSheet($this->data);
                break;

            case 'vehicles':
                $sheets[] = new VehiclesReportSheet($this->data);
                break;

            case 'spare_parts':
                $sheets[] = new SparePartsSheet($this->data);
                break;

            default:
                $sheets[] = new SummarySheet($this->data);
        }

        return $sheets;
    }
}



