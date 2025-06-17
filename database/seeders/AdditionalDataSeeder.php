<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MaintenanceOperation;
use App\Models\Vehicle;
use Carbon\Carbon;

class AdditionalDataSeeder extends Seeder
{
    public function run()
    {
        // Générer des données pour les 12 derniers mois
        $vehicles = Vehicle::all();
        $technicians = \App\Models\User::where('role', 'technician')->get();
        $chief = \App\Models\User::where('role', 'chief')->first();
        $maintenanceTypes = \App\Models\MaintenanceType::all();

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            // Générer 5-15 opérations par mois
            $operationsCount = rand(5, 15);

            for ($j = 0; $j < $operationsCount; $j++) {
                $vehicle = $vehicles->random();
                $maintenanceType = $maintenanceTypes->random();
                $technician = $technicians->random();

                $operation = MaintenanceOperation::create([
                    'vehicle_id' => $vehicle->id,
                    'maintenance_type_id' => $maintenanceType->id,
                    'technician_id' => $technician->id,
                    'operation_date' => $month->copy()->addDays(rand(0, 28)),
                    'description' => 'Opération de maintenance',
                    'labor_cost' => $maintenanceType->default_cost,
                    'parts_cost' => rand(0, 100000),
                    'status' => 'validated',
                    'validated_by' => $chief->id,
                    'validated_at' => $month->copy()->addDays(rand(1, 29)),
                ]);

                $operation->update([
                    'total_cost' => $operation->labor_cost + $operation->parts_cost
                ]);
            }
        }
    }
}
