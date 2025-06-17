<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'brand' => $this->brand,
            'model' => $this->model,
            'vehicle_type' => new VehicleTypeResource($this->whenLoaded('vehicleType')),
            'vehicle_type_id' => $this->vehicle_type_id,
            'year' => $this->year,
            'acquisition_date' => $this->acquisition_date?->format('Y-m-d'),
            'status' => $this->status,
            'under_warranty' => $this->under_warranty,
            'warranty_end_date' => $this->warranty_end_date?->format('Y-m-d'),
            'specifications' => $this->specifications,
            'last_maintenance' => new MaintenanceOperationResource($this->whenLoaded('lastMaintenance')),
            'maintenance_operations' => MaintenanceOperationResource::collection($this->whenLoaded('maintenanceOperations')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
