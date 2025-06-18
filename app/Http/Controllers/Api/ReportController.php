<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceOperation;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenanceReportExport;

class ReportController extends Controller
{
    /**
     * Générer un rapport d'export
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:excel,pdf,csv',
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'type' => 'nullable|in:summary,detailed,costs,vehicles,spare_parts'
        ]);

        $year = $request->year;
        $format = $request->format;
        $type = $request->type ?? 'summary';

        // Collecter les données selon le type de rapport
        $data = $this->getReportData($year, $type);

        switch ($format) {
            case 'excel':
                return $this->exportExcel($data, $year, $type);
            case 'pdf':
                return $this->exportPdf($data, $year, $type);
            case 'csv':
                return $this->exportCsv($data, $year, $type);
        }
    }

    /**
     * Obtenir un résumé annuel
     */
    public function annualSummary($year)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        // Statistiques générales
        $stats = [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::active()->count(),
            'total_operations' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->count(),
            'total_cost' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->sum('total_cost'),
            'average_cost_per_operation' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->avg('total_cost'),
        ];

        // Coûts mensuels - FIXED for SQLite
        $monthlyCosts = MaintenanceOperation::validated()
            ->whereBetween('operation_date', [$startDate, $endDate])
            ->select(
                DB::raw("strftime('%m', operation_date) as month"),
                DB::raw("strftime('%Y', operation_date) as year"),
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count'),
                DB::raw('SUM(labor_cost) as labor_cost'),
                DB::raw('SUM(parts_cost) as parts_cost')
            )
            ->groupBy(DB::raw("strftime('%Y', operation_date)"), DB::raw("strftime('%m', operation_date)"))
            ->orderBy(DB::raw("strftime('%m', operation_date)"))
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create()->month((int)$item->month)->format('F'),
                    'total_cost' => $item->total_cost,
                    'operations_count' => $item->operations_count,
                    'labor_cost' => $item->labor_cost,
                    'parts_cost' => $item->parts_cost,
                ];
            });

        // Coûts par catégorie
        $costsByCategory = MaintenanceOperation::where('maintenance_operations.status', 'validated')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->join('maintenance_types', 'maintenance_operations.maintenance_type_id', '=', 'maintenance_types.id')
            ->select(
                'maintenance_types.category',
                DB::raw('SUM(maintenance_operations.total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count'),
                DB::raw('AVG(maintenance_operations.total_cost) as average_cost')
            )
            ->groupBy('maintenance_types.category')
            ->get();

        // Coûts par type de véhicule - FIXED: Specify table prefix for status column
        $costsByVehicleType = MaintenanceOperation::where('maintenance_operations.status', 'validated')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->join('vehicles', 'maintenance_operations.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->select(
                'vehicle_types.name as vehicle_type',
                DB::raw('SUM(maintenance_operations.total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count'),
                DB::raw('AVG(maintenance_operations.total_cost) as average_cost')
            )
            ->groupBy('vehicle_types.id', 'vehicle_types.name')
            ->get();

        // Top 10 véhicules par coût - FIXED: Specify table prefix for status column
        $topVehiclesByCost = MaintenanceOperation::where('maintenance_operations.status', 'validated')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->join('vehicles', 'maintenance_operations.vehicle_id', '=', 'vehicles.id')
            ->select(
                'vehicles.id',
                'vehicles.registration_number',
                'vehicles.brand',
                'vehicles.model',
                DB::raw('SUM(maintenance_operations.total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count')
            )
            ->groupBy('vehicles.id', 'vehicles.registration_number', 'vehicles.brand', 'vehicles.model')
            ->orderBy('total_cost', 'desc')
            ->limit(10)
            ->get();

        // Consommation de pièces détachées
        $sparePartsConsumption = DB::table('spare_part_usages')
            ->join('maintenance_operations', 'spare_part_usages.maintenance_operation_id', '=', 'maintenance_operations.id')
            ->join('spare_parts', 'spare_part_usages.spare_part_id', '=', 'spare_parts.id')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->where('maintenance_operations.status', 'validated')
            ->select(
                'spare_parts.name',
                'spare_parts.code',
                'spare_parts.category',
                DB::raw('SUM(spare_part_usages.quantity_used) as total_quantity'),
                DB::raw('SUM(spare_part_usages.total_price) as total_value')
            )
            ->groupBy('spare_parts.id', 'spare_parts.name', 'spare_parts.code', 'spare_parts.category')
            ->orderBy('total_value', 'desc')
            ->get();

        // Tendances de maintenance
        $maintenanceTrends = $this->calculateMaintenanceTrends($year);

        return response()->json([
            'year' => $year,
            'stats' => $stats,
            'monthly_costs' => $monthlyCosts,
            'costs_by_category' => $costsByCategory,
            'costs_by_vehicle_type' => $costsByVehicleType,
            'top_vehicles_by_cost' => $topVehiclesByCost,
            'spare_parts_consumption' => $sparePartsConsumption,
            'trends' => $maintenanceTrends,
        ]);
    }

    /**
     * Rapport détaillé par véhicule
     */
    public function vehicleReport(Request $request, $vehicleId)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $vehicle = Vehicle::with('vehicleType')->findOrFail($vehicleId);

        $query = $vehicle->maintenanceOperations()
            ->with(['maintenanceType', 'technician', 'sparePartUsages.sparePart'])
            ->validated();

        if ($request->start_date) {
            $query->whereDate('operation_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('operation_date', '<=', $request->end_date);
        }

        $operations = $query->orderBy('operation_date', 'desc')->get();

        // Statistiques du véhicule
        $stats = [
            'total_operations' => $operations->count(),
            'total_cost' => $operations->sum('total_cost'),
            'average_cost' => $operations->avg('total_cost'),
            'total_labor_cost' => $operations->sum('labor_cost'),
            'total_parts_cost' => $operations->sum('parts_cost'),
            'operations_by_category' => $operations->groupBy('maintenanceType.category')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'total_cost' => $group->sum('total_cost'),
                    ];
                }),
        ];

        return response()->json([
            'vehicle' => $vehicle,
            'stats' => $stats,
            'operations' => $operations,
        ]);
    }

    /**
     * Rapport de consommation des pièces
     */
    public function sparePartsReport(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . date('Y'),
            'category' => 'nullable|in:filtration,lubrification,pneumatique,batterie,autre',
        ]);

        $year = $request->year;
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $query = DB::table('spare_part_usages')
            ->join('maintenance_operations', 'spare_part_usages.maintenance_operation_id', '=', 'maintenance_operations.id')
            ->join('spare_parts', 'spare_part_usages.spare_part_id', '=', 'spare_parts.id')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->where('maintenance_operations.status', 'validated');

        if ($request->category) {
            $query->where('spare_parts.category', $request->category);
        }

        $consumption = $query->select(
            'spare_parts.id',
            'spare_parts.code',
            'spare_parts.name',
            'spare_parts.category',
            'spare_parts.unit',
            'spare_parts.quantity_in_stock',
            DB::raw('SUM(spare_part_usages.quantity_used) as total_quantity_used'),
            DB::raw('SUM(spare_part_usages.total_price) as total_value'),
            DB::raw('COUNT(DISTINCT maintenance_operations.id) as operations_count'),
            DB::raw('AVG(spare_part_usages.unit_price) as average_unit_price')
        )
        ->groupBy(
            'spare_parts.id',
            'spare_parts.code',
            'spare_parts.name',
            'spare_parts.category',
            'spare_parts.unit',
            'spare_parts.quantity_in_stock'
        )
        ->orderBy('total_value', 'desc')
        ->get();

        // Alertes de stock
        $lowStockParts = SparePart::lowStock()->get();

        // Statistiques par catégorie
        $categoryStats = $consumption->groupBy('category')->map(function ($group, $category) {
            return [
                'category' => $category,
                'total_items' => $group->count(),
                'total_quantity' => $group->sum('total_quantity_used'),
                'total_value' => $group->sum('total_value'),
            ];
        });

        return response()->json([
            'year' => $year,
            'consumption' => $consumption,
            'low_stock_alerts' => $lowStockParts,
            'category_stats' => $categoryStats,
        ]);
    }

    /**
     * Collecter les données du rapport
     */
    private function getReportData($year, $type)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();

        $data = [
            'year' => $year,
            'generated_at' => now()->format('d/m/Y H:i'),
            'type' => $type,
        ];

        switch ($type) {
            case 'summary':
                $data['stats'] = $this->getYearlyStats($startDate, $endDate);
                $data['monthly_costs'] = $this->getMonthlyCosts($startDate, $endDate);
                $data['costs_by_category'] = $this->getCostsByCategory($startDate, $endDate);
                break;

            case 'detailed':
                $data['operations'] = MaintenanceOperation::with([
                    'vehicle.vehicleType',
                    'maintenanceType',
                    'technician',
                    'sparePartUsages.sparePart'
                ])
                ->validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->orderBy('operation_date', 'desc')
                ->get();
                break;

            case 'costs':
                $data['costs_analysis'] = $this->getDetailedCostsAnalysis($startDate, $endDate);
                break;

            case 'vehicles':
                $data['vehicles_report'] = $this->getVehiclesReport($startDate, $endDate);
                break;

            case 'spare_parts':
                $data['spare_parts_report'] = $this->getSparePartsReport($startDate, $endDate);
                break;
        }

        return $data;
    }

    /**
     * Export Excel
     */
    private function exportExcel($data, $year, $type)
    {
        return Excel::download(
            new MaintenanceReportExport($data),
            "rapport-maintenance-{$type}-{$year}.xlsx"
        );
    }

    /**
     * Export PDF
     */
    private function exportPdf($data, $year, $type)
    {
        $pdf = PDF::loadView('reports.maintenance', $data);

        return $pdf->download("rapport-maintenance-{$type}-{$year}.pdf");
    }

    /**
     * Export CSV
     */
    private function exportCsv($data, $year, $type)
    {
        $filename = "rapport-maintenance-{$type}-{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');

            // En-têtes selon le type
            if ($type === 'summary' && isset($data['monthly_costs'])) {
                fputcsv($file, ['Mois', 'Nombre d\'opérations', 'Coût total']);
                foreach ($data['monthly_costs'] as $month) {
                    fputcsv($file, [
                        $month['month'],
                        $month['operations_count'],
                        $month['total_cost']
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculer les tendances de maintenance
     */
    private function calculateMaintenanceTrends($year)
    {
        $currentYear = Carbon::create($year);
        $previousYear = $currentYear->copy()->subYear();

        $currentStats = $this->getYearlyStats(
            $currentYear->copy()->startOfYear(),
            $currentYear->copy()->endOfYear()
        );

        $previousStats = $this->getYearlyStats(
            $previousYear->copy()->startOfYear(),
            $previousYear->copy()->endOfYear()
        );

        return [
            'cost_evolution' => [
                'current' => $currentStats['total_cost'],
                'previous' => $previousStats['total_cost'],
                'variation' => $previousStats['total_cost'] > 0
                    ? (($currentStats['total_cost'] - $previousStats['total_cost']) / $previousStats['total_cost']) * 100
                    : 0,
            ],
            'operations_evolution' => [
                'current' => $currentStats['total_operations'],
                'previous' => $previousStats['total_operations'],
                'variation' => $previousStats['total_operations'] > 0
                    ? (($currentStats['total_operations'] - $previousStats['total_operations']) / $previousStats['total_operations']) * 100
                    : 0,
            ],
        ];
    }

    /**
     * Obtenir les statistiques annuelles
     */
    private function getYearlyStats($startDate, $endDate)
    {
        return [
            'total_operations' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->count(),
            'total_cost' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->sum('total_cost'),
            'total_vehicles_maintained' => MaintenanceOperation::validated()
                ->whereBetween('operation_date', [$startDate, $endDate])
                ->distinct('vehicle_id')
                ->count('vehicle_id'),
        ];
    }

    /**
     * Obtenir les coûts mensuels - FIXED for SQLite
     */
    private function getMonthlyCosts($startDate, $endDate)
    {
        return MaintenanceOperation::validated()
            ->whereBetween('operation_date', [$startDate, $endDate])
            ->select(
                DB::raw("strftime('%m', operation_date) as month"),
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count')
            )
            ->groupBy(DB::raw("strftime('%m', operation_date)"))
            ->orderBy(DB::raw("strftime('%m', operation_date)"))
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create()->month((int)$item->month)->format('F'),
                    'total_cost' => $item->total_cost,
                    'operations_count' => $item->operations_count,
                ];
            });
    }

    /**
     * Obtenir les coûts par catégorie - FIXED: Specify table prefix for status column
     */
    private function getCostsByCategory($startDate, $endDate)
    {
        return MaintenanceOperation::where('maintenance_operations.status', 'validated')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->join('maintenance_types', 'maintenance_operations.maintenance_type_id', '=', 'maintenance_types.id')
            ->select(
                'maintenance_types.category',
                DB::raw('SUM(maintenance_operations.total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count')
            )
            ->groupBy('maintenance_types.category')
            ->get();
    }

    /**
     * Analyse détaillée des coûts
     */
    private function getDetailedCostsAnalysis($startDate, $endDate)
    {
        // Implémentation détaillée de l'analyse des coûts
        return [];
    }

    /**
     * Rapport des véhicules - FIXED: Specify table prefix for status column in closure
     */
    private function getVehiclesReport($startDate, $endDate)
    {
        return Vehicle::with(['vehicleType'])
            ->withCount(['maintenanceOperations' => function ($query) use ($startDate, $endDate) {
                $query->where('maintenance_operations.status', 'validated')
                      ->whereBetween('operation_date', [$startDate, $endDate]);
            }])
            ->withSum(['maintenanceOperations' => function ($query) use ($startDate, $endDate) {
                $query->where('maintenance_operations.status', 'validated')
                      ->whereBetween('operation_date', [$startDate, $endDate]);
            }], 'total_cost')
            ->orderBy('maintenance_operations_sum_total_cost', 'desc')
            ->get();
    }

    /**
     * Rapport des pièces détachées
     */
    private function getSparePartsReport($startDate, $endDate)
    {
        return DB::table('spare_part_usages')
            ->join('maintenance_operations', 'spare_part_usages.maintenance_operation_id', '=', 'maintenance_operations.id')
            ->join('spare_parts', 'spare_part_usages.spare_part_id', '=', 'spare_parts.id')
            ->whereBetween('maintenance_operations.operation_date', [$startDate, $endDate])
            ->where('maintenance_operations.status', 'validated')
            ->select(
                'spare_parts.*',
                DB::raw('SUM(spare_part_usages.quantity_used) as total_used'),
                DB::raw('SUM(spare_part_usages.total_price) as total_value')
            )
            ->groupBy('spare_parts.id')
            ->orderBy('total_value', 'desc')
            ->get();
    }
}
