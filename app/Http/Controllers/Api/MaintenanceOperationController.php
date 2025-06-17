<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaintenanceOperationRequest;
use App\Http\Resources\MaintenanceOperationResource;
use App\Models\MaintenanceOperation;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceOperationController extends Controller
{
    public function index(Request $request)
    {
        $operations = MaintenanceOperation::with([
            'vehicle.vehicleType',
            'maintenanceType',
            'technician',
            'sparePartUsages.sparePart'
        ])
        ->when($request->user()->isTechnician(), function ($query) use ($request) {
            return $query->where('technician_id', $request->user()->id);
        })
        ->when($request->status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($request->vehicle_id, function ($query, $vehicleId) {
            return $query->where('vehicle_id', $vehicleId);
        })
        ->when($request->date_from, function ($query, $dateFrom) {
            return $query->whereDate('operation_date', '>=', $dateFrom);
        })
        ->when($request->date_to, function ($query, $dateTo) {
            return $query->whereDate('operation_date', '<=', $dateTo);
        })
        ->orderBy('operation_date', 'desc')
        ->paginate($request->per_page ?? 15);

        return MaintenanceOperationResource::collection($operations);
    }

    public function store(StoreMaintenanceOperationRequest $request)
    {
        DB::beginTransaction();

        try {
            // Créer l'opération de maintenance
            $operationData = $request->except('spare_parts');
            $operationData['technician_id'] = $request->user()->id;

            // Calculer le coût de main d'œuvre basé sur le type de maintenance
            $maintenanceType = \App\Models\MaintenanceType::find($request->maintenance_type_id);
            $operationData['labor_cost'] = $maintenanceType->default_cost;

            $operation = MaintenanceOperation::create($operationData);

            // Gérer les pièces détachées utilisées
            $totalPartsCost = 0;
            if ($request->has('spare_parts')) {
                foreach ($request->spare_parts as $partData) {
                    $sparePart = SparePart::find($partData['spare_part_id']);

                    // Vérifier la disponibilité en stock
                    if ($sparePart->quantity_in_stock < $partData['quantity_used']) {
                        throw new \Exception("Stock insuffisant pour {$sparePart->name}");
                    }

                    $usage = $operation->sparePartUsages()->create([
                        'spare_part_id' => $sparePart->id,
                        'quantity_used' => $partData['quantity_used'],
                        'unit_price' => $sparePart->unit_price,
                    ]);

                    $totalPartsCost += $usage->total_price;
                }
            }

            // Mettre à jour le coût total
            $operation->update(['parts_cost' => $totalPartsCost]);

            DB::commit();

            return new MaintenanceOperationResource(
                $operation->load(['vehicle', 'maintenanceType', 'sparePartUsages.sparePart'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(MaintenanceOperation $maintenanceOperation)
    {
        return new MaintenanceOperationResource(
            $maintenanceOperation->load([
                'vehicle.vehicleType',
                'maintenanceType',
                'technician',
                'validator',
                'sparePartUsages.sparePart'
            ])
        );
    }

    public function plannedOperations(Request $request)
    {
        // Retourner les opérations planifiées basées sur l'historique et les intervalles
        $vehicles = \App\Models\Vehicle::active()->get();
        $plannedOperations = [];

        foreach ($vehicles as $vehicle) {
            $lastMaintenance = $vehicle->maintenanceOperations()
                ->whereHas('maintenanceType', function ($query) {
                    $query->where('category', 'preventive');
                })
                ->latest('operation_date')
                ->first();

            // Logique pour déterminer la prochaine maintenance
            // (simplifiée pour cet exemple)
            $nextDate = $lastMaintenance
                ? $lastMaintenance->operation_date->addMonths(3)
                : now();

            if ($nextDate->isPast() || $nextDate->isToday() || $nextDate->isFuture() && $nextDate->diffInDays(now()) <= 30) {
                $plannedOperations[] = [
                    'vehicle' => $vehicle,
                    'next_maintenance_date' => $nextDate,
                    'maintenance_types' => \App\Models\MaintenanceType::preventive()->get(),
                ];
            }
        }

        return response()->json(['data' => $plannedOperations]);
    }
}
