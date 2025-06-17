<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;


class MaintenanceOperationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'vehicle_id' => $this->vehicle_id,
            'maintenance_type' => new MaintenanceTypeResource($this->whenLoaded('maintenanceType')),
            'maintenance_type_id' => $this->maintenance_type_id,
            'technician' => new UserResource($this->whenLoaded('technician')),
            'technician_id' => $this->technician_id,
            'operation_date' => $this->operation_date->format('Y-m-d'),
            'description' => $this->description,
            'labor_cost' => $this->labor_cost,
            'parts_cost' => $this->parts_cost,
            'total_cost' => $this->total_cost,
            'status' => $this->status,
            'validator' => new UserResource($this->whenLoaded('validator')),
            'validated_by' => $this->validated_by,
            'validated_at' => $this->validated_at?->format('Y-m-d H:i:s'),
            'validation_comment' => $this->validation_comment,
            'spare_part_usages' => SparePartUsageResource::collection($this->whenLoaded('sparePartUsages')),
            'additional_data' => $this->additional_data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
