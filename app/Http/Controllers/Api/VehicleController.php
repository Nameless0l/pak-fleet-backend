<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validated();
        
        // Gérer l'upload de l'image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('vehicles', 'public');
            $data['image_path'] = $imagePath;
        }

        $vehicle = Vehicle::create($data);

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
        $data = $request->validated();
        
        // Gérer l'upload de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            
            $image = $request->file('image');
            $imagePath = $image->store('vehicles', 'public');
            $data['image_path'] = $imagePath;
        }

        $vehicle->update($data);

        return new VehicleResource($vehicle->load('vehicleType'));
    }

    public function destroy(Vehicle $vehicle)
    {
        // Supprimer l'image si elle existe
        if ($vehicle->image_path && Storage::disk('public')->exists($vehicle->image_path)) {
            Storage::disk('public')->delete($vehicle->image_path);
        }
        
        $vehicle->delete();

        return response()->json(['message' => 'Véhicule supprimé avec succès']);
    }
    
    public function analytics()
    {
        $analytics = [
            'total_vehicles' => Vehicle::count(),
            'active_vehicles' => Vehicle::where('status', 'active')->count(),
            'maintenance_vehicles' => Vehicle::where('status', 'maintenance')->count(),
            'out_of_service_vehicles' => Vehicle::where('status', 'out_of_service')->count(),
            'vehicles_by_type' => Vehicle::with('vehicleType')
                ->get()
                ->groupBy('vehicleType.name')
                ->map->count(),
            'recent_maintenances' => Vehicle::with(['maintenanceOperations' => function($query) {
                $query->latest()->limit(5);
            }, 'maintenanceOperations.maintenanceType'])->get()
        ];

        return response()->json($analytics);
    }
}