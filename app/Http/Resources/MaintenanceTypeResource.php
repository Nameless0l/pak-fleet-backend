<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'default_cost' => $this->default_cost,
        ];
    }
}
