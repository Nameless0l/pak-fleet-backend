<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'brand',
        'model',
        'vehicle_type_id',
        'year',
        'acquisition_date',
        'status',
        'under_warranty',
        'warranty_end_date',
        'specifications',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'warranty_end_date' => 'date',
        'under_warranty' => 'boolean',
        'specifications' => 'array',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function maintenanceOperations()
    {
        return $this->hasMany(MaintenanceOperation::class);
    }

    public function lastMaintenance()
    {
        return $this->hasOne(MaintenanceOperation::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUnderWarranty($query)
    {
        return $query->where('under_warranty', true)
                     ->where('warranty_end_date', '>=', now());
    }
}
