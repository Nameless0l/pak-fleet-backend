<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'description',
        'default_cost',
    ];

    protected $casts = [
        'default_cost' => 'decimal:2',
    ];

    public function maintenanceOperations()
    {
        return $this->hasMany(MaintenanceOperation::class);
    }

    public function scopePreventive($query)
    {
        return $query->where('category', 'preventive');
    }

    public function scopeCorrective($query)
    {
        return $query->where('category', 'corrective');
    }

    public function scopeAmeliorative($query)
    {
        return $query->where('category', 'ameliorative');
    }
}
