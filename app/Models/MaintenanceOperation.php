<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'maintenance_type_id',
        'technician_id',
        'operation_date',
        'description',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'status',
        'validated_by',
        'validated_at',
        'validation_comment',
        'additional_data',
    ];

    protected $casts = [
        'operation_date' => 'date',
        'validated_at' => 'datetime',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'additional_data' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($operation) {
            $operation->calculateTotalCost();
        });

        static::updating(function ($operation) {
            $operation->calculateTotalCost();
        });
    }

    public function calculateTotalCost()
    {
        $this->total_cost = $this->labor_cost + $this->parts_cost;
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceType()
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function sparePartUsages()
    {
        return $this->hasMany(SparePartUsage::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('operation_date', $year)
                     ->whereMonth('operation_date', $month);
    }
}

