<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparePartUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_operation_id',
        'spare_part_id',
        'quantity_used',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity_used' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($usage) {
            $usage->total_price = $usage->quantity_used * $usage->unit_price;

            // Décrémenter le stock
            $usage->sparePart->decrementStock($usage->quantity_used);
        });
    }

    public function maintenanceOperation()
    {
        return $this->belongsTo(MaintenanceOperation::class);
    }

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class);
    }
}
