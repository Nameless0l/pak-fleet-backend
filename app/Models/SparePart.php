<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'unit',
        'unit_price',
        'quantity_in_stock',
        'minimum_stock',
        'category',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'minimum_stock' => 'integer',
    ];

    public function usages()
    {
        return $this->hasMany(SparePartUsage::class);
    }

    public function isLowStock()
    {
        return $this->quantity_in_stock <= $this->minimum_stock;
    }

    public function decrementStock($quantity)
    {
        $this->decrement('quantity_in_stock', $quantity);
    }

    public function incrementStock($quantity)
    {
        $this->increment('quantity_in_stock', $quantity);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= minimum_stock');
    }
}
