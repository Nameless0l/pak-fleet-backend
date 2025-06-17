<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SparePartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'quantity_in_stock' => $this->quantity_in_stock,
            'minimum_stock' => $this->minimum_stock,
            'category' => $this->category,
            'is_low_stock' => $this->isLowStock(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
