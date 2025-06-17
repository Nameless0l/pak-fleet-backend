<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class SparePartUsageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'spare_part' => new SparePartResource($this->whenLoaded('sparePart')),
            'spare_part_id' => $this->spare_part_id,
            'quantity_used' => $this->quantity_used,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
        ];
    }
}
