<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with(['vehicleType', 'lastMaintenance'])
            ->when($request->search, function ($query, $search) {
                return $query->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            })
            ->when($request->type_id, function ($query, $typeId) {
                return $query->where('vehicle_type_id', $typeId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('registration_number')
            ->paginate($request->per_page ?? 15);

        return VehicleResource::collection($vehicles);
    }

    public function store(StoreVehicleRequest $request)
    {
        $vehicle = Vehicle::create($request->validated());

        return new VehicleResource($vehicle->load('vehicleType'));
    }

    public function show(Vehicle $vehicle)
    {
        return new VehicleResource(
            $vehicle->load(['vehicleType', 'maintenanceOperations.maintenanceType'])
        );
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());

        return new VehicleResource($vehicle->load('vehicleType'));
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json(['message' => 'Véhicule supprimé avec succès']);
    }
}
