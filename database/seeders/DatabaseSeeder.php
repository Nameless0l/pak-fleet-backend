<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Vehicle;
use App\Models\SparePart;
use App\Models\SparePartUsage;
use Illuminate\Database\Seeder;
use App\Models\MaintenanceOperation;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $chief = User::create([
            'name' => 'Chef de Service',
            'email' => 'chef@pak.cm',
            'password' => Hash::make('password123'),
            'role' => 'chief',
            'employee_id' => 'PAK001',
            'is_active' => true,
        ]);
        $technician1 = User::create([
            'name' => 'Jean Technicien',
            'email' => 'jean@pak.cm',
            'password' => Hash::make('password123'),
            'role' => 'technician',
            'employee_id' => 'PAK002',
            'is_active' => true,
        ]);

        $technician2 = User::create([
            'name' => 'Marie Technicien',
            'email' => 'marie@pak.cm',
            'password' => Hash::make('password123'),
            'role' => 'technician',
            'employee_id' => 'PAK003',
            'is_active' => true,
        ]);
        $this->call([
            MaintenanceOperationSeeder::class,
        ]);
    }
    // public function run(): void
    // {
    //     // User::factory(10)->create();


    //      $chief = User::create([
    //         'name' => 'Chef de Service',
    //         'email' => 'chef@pak.cm',
    //         'password' => Hash::make('password123'),
    //         'role' => 'chief',
    //         'employee_id' => 'PAK001',
    //         'is_active' => true,
    //     ]);



    //     // Créer des véhicules
    //     $vehicles = [
    //         // Station Wagon
    //         ['registration_number' => 'CE-001-PAK', 'brand' => 'Toyota', 'model' => 'Camry', 'vehicle_type_id' => 1, 'year' => 2022, 'status' => 'active'],

    //         // Crossover
    //         ['registration_number' => 'CE-002-PAK', 'brand' => 'Toyota', 'model' => 'RAV4', 'vehicle_type_id' => 2, 'year' => 2021, 'status' => 'active'],
    //         ['registration_number' => 'CE-003-PAK', 'brand' => 'Hyundai', 'model' => 'Creta', 'vehicle_type_id' => 2, 'year' => 2022, 'status' => 'active'],

    //         // SUV
    //         ['registration_number' => 'CE-004-PAK', 'brand' => 'Toyota', 'model' => 'Land Cruiser', 'vehicle_type_id' => 3, 'year' => 2021, 'status' => 'active'],
    //         ['registration_number' => 'CE-005-PAK', 'brand' => 'Ford', 'model' => 'Everest', 'vehicle_type_id' => 3, 'year' => 2020, 'status' => 'maintenance'],

    //         // Tout Terrain
    //         ['registration_number' => 'CE-006-PAK', 'brand' => 'Toyota', 'model' => 'Hilux', 'vehicle_type_id' => 5, 'year' => 2021, 'status' => 'active'],
    //         ['registration_number' => 'CE-007-PAK', 'brand' => 'Ford', 'model' => 'Ranger', 'vehicle_type_id' => 5, 'year' => 2022, 'status' => 'active'],

    //         // Coaster
    //         ['registration_number' => 'CE-008-PAK', 'brand' => 'Toyota', 'model' => 'Coaster', 'vehicle_type_id' => 6, 'year' => 2020, 'status' => 'active'],
    //     ];

    //     foreach ($vehicles as $vehicleData) {
    //         Vehicle::create($vehicleData);
    //     }

    //     // Créer des pièces détachées
    //     $spareParts = [
    //         // Filtration
    //         ['code' => 'FH001', 'name' => 'Filtre à huile Toyota', 'unit' => 'pièce', 'unit_price' => 15000, 'quantity_in_stock' => 50, 'minimum_stock' => 10, 'category' => 'filtration'],
    //         ['code' => 'FA001', 'name' => 'Filtre à air Toyota', 'unit' => 'pièce', 'unit_price' => 25000, 'quantity_in_stock' => 30, 'minimum_stock' => 10, 'category' => 'filtration'],
    //         ['code' => 'FC001', 'name' => 'Filtre carburant Toyota', 'unit' => 'pièce', 'unit_price' => 35000, 'quantity_in_stock' => 25, 'minimum_stock' => 8, 'category' => 'filtration'],

    //         // Lubrification
    //         ['code' => 'HM001', 'name' => 'Huile moteur 5W30', 'unit' => 'litre', 'unit_price' => 8000, 'quantity_in_stock' => 200, 'minimum_stock' => 50, 'category' => 'lubrification'],
    //         ['code' => 'HB001', 'name' => 'Huile boîte de vitesse', 'unit' => 'litre', 'unit_price' => 12000, 'quantity_in_stock' => 100, 'minimum_stock' => 30, 'category' => 'lubrification'],
    //         ['code' => 'LF001', 'name' => 'Liquide de frein DOT4', 'unit' => 'litre', 'unit_price' => 15000, 'quantity_in_stock' => 40, 'minimum_stock' => 15, 'category' => 'lubrification'],

    //         // Pneumatique
    //         ['code' => 'PN001', 'name' => 'Pneu 265/65 R17', 'unit' => 'pièce', 'unit_price' => 150000, 'quantity_in_stock' => 20, 'minimum_stock' => 8, 'category' => 'pneumatique'],
    //         ['code' => 'PN002', 'name' => 'Pneu 225/65 R17', 'unit' => 'pièce', 'unit_price' => 120000, 'quantity_in_stock' => 16, 'minimum_stock' => 8, 'category' => 'pneumatique'],

    //         // Batterie
    //         ['code' => 'BA001', 'name' => 'Batterie 12V 70Ah', 'unit' => 'pièce', 'unit_price' => 85000, 'quantity_in_stock' => 10, 'minimum_stock' => 5, 'category' => 'batterie'],
    //         ['code' => 'BA002', 'name' => 'Batterie 12V 100Ah', 'unit' => 'pièce', 'unit_price' => 120000, 'quantity_in_stock' => 8, 'minimum_stock' => 4, 'category' => 'batterie'],
    //     ];

    //     foreach ($spareParts as $partData) {
    //         SparePart::create($partData);
    //     }

    //     // Créer des opérations de maintenance
    //     $vehicles = Vehicle::all();
    //     $maintenanceTypes = \App\Models\MaintenanceType::all();
    //     $spareParts = SparePart::all();

    //     foreach ($vehicles as $vehicle) {
    //         // Maintenance préventive validée
    //         $operation = MaintenanceOperation::create([
    //             'vehicle_id' => $vehicle->id,
    //             'maintenance_type_id' => $maintenanceTypes->where('category', 'preventive')->random()->id,
    //             'technician_id' => $technician1->id,
    //             'operation_date' => now()->subDays(rand(30, 90)),
    //             'description' => 'Maintenance préventive régulière',
    //             'labor_cost' => 75000,
    //             'parts_cost' => 0,
    //             'status' => 'validated',
    //             'validated_by' => $chief->id,
    //             'validated_at' => now()->subDays(rand(25, 85)),
    //             'validation_comment' => 'Opération conforme',
    //         ]);

    //         // Ajouter des pièces utilisées
    //         $filterPart = $spareParts->where('category', 'filtration')->random();
    //         $oilPart = $spareParts->where('category', 'lubrification')->first();

    //         SparePartUsage::create([
    //             'maintenance_operation_id' => $operation->id,
    //             'spare_part_id' => $filterPart->id,
    //             'quantity_used' => 1,
    //             'unit_price' => $filterPart->unit_price,
    //         ]);

    //         SparePartUsage::create([
    //             'maintenance_operation_id' => $operation->id,
    //             'spare_part_id' => $oilPart->id,
    //             'quantity_used' => rand(4, 6),
    //             'unit_price' => $oilPart->unit_price,
    //         ]);

    //         // Recalculer le coût total
    //         $partsCost = $operation->sparePartUsages->sum('total_price');
    //         $operation->update([
    //             'parts_cost' => $partsCost,
    //             'total_cost' => $operation->labor_cost + $partsCost,
    //         ]);

    //         // Maintenance corrective en attente (pour certains véhicules)
    //         if (rand(0, 1)) {
    //             MaintenanceOperation::create([
    //                 'vehicle_id' => $vehicle->id,
    //                 'maintenance_type_id' => $maintenanceTypes->where('category', 'corrective')->random()->id,
    //                 'technician_id' => $technician2->id,
    //                 'operation_date' => now()->subDays(rand(1, 7)),
    //                 'description' => 'Réparation suite à panne',
    //                 'labor_cost' => rand(150000, 300000),
    //                 'parts_cost' => rand(50000, 150000),
    //                 'status' => 'pending',
    //             ]);
    //         }
    //     }

    //     // Mettre certaines pièces en stock faible
    //     SparePart::whereIn('code', ['FA001', 'PN002'])->update([
    //         'quantity_in_stock' => 5
    //     ]);
    // }
}
