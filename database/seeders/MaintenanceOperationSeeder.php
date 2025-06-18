<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\MaintenanceType;
use App\Models\MaintenanceOperation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MaintenanceOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les techniciens supplémentaires si nécessaire
        $this->createTechnicians();
        
        // Créer les véhicules manquants
        $this->createVehicles();
        
        // Importer les opérations de maintenance depuis le CSV
        $this->importMaintenanceOperations();
    }
    
    /**
     * Créer les techniciens supplémentaires
     */
    private function createTechnicians(): void
    {
        // Les techniciens 1, 2, 3 existent déjà dans DatabaseSeeder
        // Créer les techniciens 4, 5, 6, 7
        $technicians = [
            ['id' => 4, 'name' => 'Paul Technicien', 'email' => 'paul@pak.cm', 'employee_id' => 'PAK004'],
            ['id' => 5, 'name' => 'Pierre Technicien', 'email' => 'pierre@pak.cm', 'employee_id' => 'PAK005'],
            ['id' => 6, 'name' => 'Jacques Technicien', 'email' => 'jacques@pak.cm', 'employee_id' => 'PAK006'],
            ['id' => 7, 'name' => 'Sophie Technicien', 'email' => 'sophie@pak.cm', 'employee_id' => 'PAK007'],
        ];
        
        foreach ($technicians as $tech) {
            User::firstOrCreate(
                ['email' => $tech['email']],
                [
                    'id' => $tech['id'],
                    'name' => $tech['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'technician',
                    'employee_id' => $tech['employee_id'],
                    'is_active' => true,
                ]
            );
        }
    }
    
    /**
     * Créer les véhicules manquants basés sur les numéros de chassis du CSV
     */
    private function createVehicles(): void
    {
        // Récupérer le chemin du fichier CSV
        $csvPath = database_path('seeders/data/Maintenance.csv');
        
        // Si le fichier n'existe pas dans le dossier seeders/data, essayer de le lire depuis storage
        if (!file_exists($csvPath)) {
            $csvPath = storage_path('app/Maintenance.csv');
        }
        
        if (!file_exists($csvPath)) {
            $this->command->error("Le fichier Maintenance.csv n'a pas été trouvé. Veuillez le placer dans database/seeders/data/ ou storage/app/");
            return;
        }
        
        // Lire le CSV
        $csv = array_map('str_getcsv', file($csvPath));
        $headers = array_shift($csv);
        
        // Créer un tableau associatif pour chaque ligne
        $data = [];
        foreach ($csv as $row) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        // Obtenir les numéros de chassis uniques
        $chassisNumbers = array_unique(array_column($data, 'numéro_chassis'));
        
        // Types de véhicules disponibles
        $vehicleTypes = VehicleType::all();
        $brands = ['Toyota', 'Hyundai', 'Ford', 'Nissan', 'Mitsubishi', 'Mercedes', 'Volkswagen'];
        $models = ['Hilux', 'Land Cruiser', 'RAV4', 'Creta', 'Everest', 'Patrol', 'Pajero', 'Sprinter', 'Amarok'];
        
        foreach ($chassisNumbers as $chassis) {
            if (empty($chassis)) continue;
            
            // Vérifier si le véhicule existe déjà
            $exists = Vehicle::where('registration_number', 'LIKE', '%' . $chassis . '%')->exists();
            
            if (!$exists) {
                // Créer un nouveau véhicule
                Vehicle::create([
                    'registration_number' => 'CE-' . substr($chassis, 2) . '-PAK',
                    'brand' => $brands[array_rand($brands)],
                    'model' => $models[array_rand($models)],
                    'vehicle_type_id' => $vehicleTypes->random()->id,
                    'year' => rand(2018, 2023),
                    'acquisition_date' => Carbon::now()->subDays(rand(365, 1825)),
                    'status' => 'active',
                    'under_warranty' => false,
                    'specifications' => json_encode(['chassis_number' => $chassis])
                ]);
            }
        }
        
        $this->command->info("Véhicules créés avec succès.");
    }
    
    /**
     * Importer les opérations de maintenance depuis le CSV
     */
    private function importMaintenanceOperations(): void
    {
        // Récupérer le chemin du fichier CSV
        $csvPath = database_path('seeders/data/Maintenance.csv');
        
        if (!file_exists($csvPath)) {
            $csvPath = storage_path('app/Maintenance.csv');
        }
        
        if (!file_exists($csvPath)) {
            $this->command->error("Le fichier Maintenance.csv n'a pas été trouvé.");
            return;
        }
        
        // Lire le CSV
        $csv = array_map('str_getcsv', file($csvPath));
        $headers = array_shift($csv);
        
        // Créer un tableau associatif pour chaque ligne
        $data = [];
        foreach ($csv as $row) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        // Récupérer les types de maintenance
        $maintenanceTypes = MaintenanceType::all()->keyBy('name');
        
        // Récupérer le chef de service pour validation
        $chief = User::where('role', 'chief')->first();
        
        // Mapper les libellés de maintenance aux types existants
        $maintenanceMapping = $this->getMaintenanceTypeMapping();
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($data as $row) {
            try {
                // Ignorer les lignes avec des données manquantes critiques
                if (empty($row['numéro_chassis']) || empty($row['Date_maintenance'])) {
                    $skipped++;
                    continue;
                }
                
                // Trouver le véhicule
                $vehicle = Vehicle::where('registration_number', 'LIKE', '%' . substr($row['numéro_chassis'], 2) . '%')
                    ->orWhereJsonContains('specifications->chassis_number', $row['numéro_chassis'])
                    ->first();
                
                if (!$vehicle) {
                    $this->command->warn("Véhicule non trouvé pour le chassis: " . $row['numéro_chassis']);
                    $skipped++;
                    continue;
                }
                
                // Trouver le technicien
                $technician = User::find($row['Id_Techniscien']);
                if (!$technician) {
                    $this->command->warn("Technicien non trouvé avec l'ID: " . $row['Id_Techniscien']);
                    $skipped++;
                    continue;
                }
                
                // Déterminer le type de maintenance
                $maintenanceTypeId = $this->determineMaintenanceType($row['Libelle_maintenance'], $row['Nature de l\'opération'], $maintenanceTypes, $maintenanceMapping);
                
                // Parser la date
                $operationDate = $this->parseDate($row['Date_maintenance']);
                
                if (!$operationDate) {
                    $this->command->warn("Date invalide: " . $row['Date_maintenance']);
                    $skipped++;
                    continue;
                }
                
                // Calculer les coûts aléatoires mais réalistes
                $laborCost = rand(50000, 150000);
                $partsCost = $this->calculatePartsCost($row['Libelle_maintenance']);
                
                // Créer l'opération de maintenance
                $operation = MaintenanceOperation::create([
                    'vehicle_id' => $vehicle->id,
                    'maintenance_type_id' => $maintenanceTypeId,
                    'technician_id' => $technician->id,
                    'operation_date' => $operationDate,
                    'description' => $row['Libelle_maintenance'],
                    'labor_cost' => $laborCost,
                    'parts_cost' => $partsCost,
                    'total_cost' => $laborCost + $partsCost,
                    'status' => 'validated', // Toutes les opérations importées sont considérées comme validées
                    'validated_by' => $chief->id,
                    'validated_at' => $operationDate->copy()->addDays(rand(1, 5)),
                    'validation_comment' => 'Importé depuis fichier CSV historique',
                    'additional_data' => json_encode([
                        'import_id' => $row['Id_maintenance'],
                        'original_nature' => $row['Nature de l\'opération']
                    ])
                ]);
                
                $imported++;
                
            } catch (\Exception $e) {
                $this->command->error("Erreur lors de l'import de la ligne: " . $e->getMessage());
                $skipped++;
            }
        }
        
        $this->command->info("Import terminé: $imported opérations importées, $skipped lignes ignorées.");
    }
    
    /**
     * Mapper les libellés de maintenance aux types existants
     */
    private function getMaintenanceTypeMapping(): array
    {
        return [
            'vidange' => 'Vidange moteur',
            'filtre' => 'Changement filtre à huile',
            'amortisseur' => 'Réparation transmission',
            'rotule' => 'Réparation transmission',
            'suspension' => 'Réparation transmission',
            'diagnostic' => 'Inspection planifiée',
            'liquide' => 'Vidange moteur',
            'lave glace' => 'Vidange moteur',
            'peinture' => 'Réfection peinture',
            'carrosserie' => 'Réhabilitation carrosserie',
            'frein' => 'Réparation transmission',
            'embrayage' => 'Réparation transmission',
            'boite' => 'Réparation transmission',
            'électrique' => 'Réparation système électrique',
            'batterie' => 'Réparation système électrique',
            'climatisation' => 'Réparation système électrique',
        ];
    }
    
    /**
     * Déterminer le type de maintenance basé sur le libellé et la nature
     */
    private function determineMaintenanceType($libelle, $nature, $maintenanceTypes, $mapping): int
    {
        $libelleLower = strtolower($libelle);
        
        // Chercher dans le mapping
        foreach ($mapping as $keyword => $typeName) {
            if (strpos($libelleLower, $keyword) !== false) {
                if (isset($maintenanceTypes[$typeName])) {
                    return $maintenanceTypes[$typeName]->id;
                }
            }
        }
        
        // Si pas trouvé dans le mapping, utiliser la nature de l'opération
        $natureLower = strtolower($nature ?? 'préventive');
        
        if (strpos($natureLower, 'corrective') !== false) {
            return $maintenanceTypes['Réparation moteur']->id;
        } elseif (strpos($natureLower, 'préventive') !== false) {
            return $maintenanceTypes['Vidange moteur']->id;
        }
        
        // Par défaut, inspection planifiée
        return $maintenanceTypes['Inspection planifiée']->id;
    }
    
    /**
     * Parser la date depuis différents formats possibles
     */
    private function parseDate($dateString): ?Carbon
    {
        try {
            // Essayer différents formats
            $formats = [
                'D M d H:i:s T Y',  // Format dans le CSV: "Fri Feb 02 00:00:00 CET 2024"
                'Y-m-d H:i:s',
                'Y-m-d',
                'd/m/Y',
                'm/d/Y'
            ];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString);
                } catch (\Exception $e) {
                    // Continuer avec le format suivant
                }
            }
            
            // Si aucun format ne marche, essayer parse
            return Carbon::parse($dateString);
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Calculer le coût des pièces basé sur le libellé
     */
    private function calculatePartsCost($libelle): float
    {
        $libelleLower = strtolower($libelle);
        $cost = 0;
        
        // Définir des coûts approximatifs pour différentes pièces
        $partsCosts = [
            'filtre' => 45000,
            'huile' => 80000,
            'amortisseur' => 250000,
            'rotule' => 150000,
            'frein' => 120000,
            'batterie' => 100000,
            'pneu' => 150000,
            'courroie' => 75000,
            'bougie' => 40000,
            'liquide' => 35000,
            'lave glace' => 15000,
        ];
        
        foreach ($partsCosts as $part => $partCost) {
            if (strpos($libelleLower, $part) !== false) {
                $cost += $partCost;
            }
        }
        
        // Si aucune pièce spécifique trouvée, coût aléatoire
        if ($cost == 0 && strpos($libelleLower, 'remplacement') !== false) {
            $cost = rand(50000, 200000);
        }
        
        return $cost;
    }
}