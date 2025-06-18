<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceOperation;
use App\Models\Vehicle;
use App\Models\SparePart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;

        return response()->json([
            'stats' => $this->getGeneralStats(),
            'monthly_costs' => $this->getMonthlyCosts($year),
            'costs_by_category' => $this->getCostsByCategory($year),
            'costs_by_vehicle_type' => $this->getCostsByVehicleType($year),
            'upcoming_maintenance' => $this->getUpcomingMaintenance(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
        ]);
    }

    private function getGeneralStats()
    {
        return [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::active()->count(),
            'total_operations_this_month' => MaintenanceOperation::whereMonth('operation_date', now()->month)
                ->whereYear('operation_date', now()->year)
                ->count(),
            'pending_validations' => MaintenanceOperation::pending()->count(),
            'total_cost_this_month' => MaintenanceOperation::validated()
                ->whereMonth('operation_date', now()->month)
                ->whereYear('operation_date', now()->year)
                ->sum('total_cost'),
        ];
    }

    private function getMonthlyCosts($year)
    {
        return MaintenanceOperation::validated()
            ->whereYear('operation_date', $year)
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

    private function getCostsByCategory($year)
    {
        return MaintenanceOperation::validated()
            ->whereYear('operation_date', $year)
            ->join('maintenance_types', 'maintenance_operations.maintenance_type_id', '=', 'maintenance_types.id')
            ->select(
                'maintenance_types.category',
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count')
            )
            ->groupBy('maintenance_types.category')
            ->get();
    }

    private function getCostsByVehicleType($year)
    {
        return MaintenanceOperation::whereYear('operation_date', $year)
            ->where('maintenance_operations.status', 'validated')
            ->join('vehicles', 'maintenance_operations.vehicle_id', '=', 'vehicles.id')
            ->join('vehicle_types', 'vehicles.vehicle_type_id', '=', 'vehicle_types.id')
            ->select(
                'vehicle_types.name as vehicle_type',
                DB::raw('SUM(maintenance_operations.total_cost) as total_cost'),
                DB::raw('COUNT(*) as operations_count')
            )
            ->groupBy('vehicle_types.id', 'vehicle_types.name')
            ->get();
    }
    private function getUpcomingMaintenance()
    {
        // Logique simplifiée pour les maintenances à venir
        return Vehicle::active()
            ->with('lastMaintenance')
            ->get()
            ->filter(function ($vehicle) {
                $lastMaintenance = $vehicle->lastMaintenance;
                if (!$lastMaintenance) return true;

                $daysSinceLastMaintenance = $lastMaintenance->operation_date->diffInDays(now());
                return $daysSinceLastMaintenance >= 75;
            })
            ->take(10)
            ->values();
    }

    private function getLowStockAlerts()
    {
        return SparePart::lowStock()
            ->select('id', 'name', 'quantity_in_stock', 'minimum_stock')
            ->get();
    }
}
