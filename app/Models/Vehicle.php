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
        'image_path' // Ajout du champ image_path
    ];

    protected $casts = [
        'year' => 'integer',
        'acquisition_date' => 'date',
        'warranty_end_date' => 'date',
        'under_warranty' => 'boolean',
        'specifications' => 'array',
    ];

    protected $appends = ['full_image_url'];

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getFullImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Relation avec le type de véhicule
     */
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    /**
     * Relation avec les opérations de maintenance
     */
    public function maintenanceOperations()
    {
        return $this->hasMany(MaintenanceOperation::class);
    }

    /**
     * Obtenir la dernière maintenance
     */
    public function lastMaintenance()
    {
        return $this->hasOne(MaintenanceOperation::class)->latestOfMany('operation_date');
    }

    /**
     * Scope pour les véhicules actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les véhicules en maintenance
     */
    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope pour les véhicules hors service
     */
    public function scopeOutOfService($query)
    {
        return $query->where('status', 'out_of_service');
    }

    /**
     * Scope pour les véhicules sous garantie
     */
    public function scopeUnderWarranty($query)
    {
        return $query->where('under_warranty', true)
            ->where('warranty_end_date', '>=', now());
    }
}