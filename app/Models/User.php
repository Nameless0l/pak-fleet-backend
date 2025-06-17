<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isChief()
    {
        return $this->role === 'chief';
    }

    public function isTechnician()
    {
        return $this->role === 'technician';
    }

    public function maintenanceOperations()
    {
        return $this->hasMany(MaintenanceOperation::class, 'technician_id');
    }

    public function validatedOperations()
    {
        return $this->hasMany(MaintenanceOperation::class, 'validated_by');
    }
}
