<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver les contraintes de clés étrangères temporairement
        Schema::disableForeignKeyConstraints();

        // Vider les tables existantes
        DB::table('maintenances')->truncate();
        DB::table('techniciens')->truncate();
        DB::table('vehicules')->truncate();

        // Lire le fichier CSV
        $csvFile = database_path('seeders/data/Maintenance.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("Le fichier CSV n'existe pas : " . $csvFile);
            return;
        }

        $file = fopen($csvFile, 'r');
        $headers = fgetcsv($file);
        
        // Normaliser les en-têtes (enlever les espaces et caractères spéciaux)
        $headers = array_map(function($header) {
            return trim(str_replace(['é', 'è', 'ê', 'ë'], 'e', $header));
        }, $headers);

        $techniciensData = [];
        $vehiculesData = [];
        $maintenancesData = [];
        
        $techniciensProcessed = [];
        $vehiculesProcessed = [];

        while (($row = fgetcsv($file)) !== FALSE) {
            $data = array_combine($headers, $row);
            
            // Traiter les techniciens
            $technicienId = $data['Id_Techniscien'];
            if (!empty($technicienId) && !in_array($technicienId, $techniciensProcessed)) {
                $techniciensData[] = [
                    'id' => $technicienId,
                    'nom' => 'Technicien ' . $technicienId,
                    'prenom' => 'Agent',
                    'email' => 'technicien' . $technicienId . '@maintenance.com',
                    'telephone' => '06' . str_pad($technicienId, 8, '0', STR_PAD_LEFT),
                    'specialite' => $this->getSpecialite($technicienId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $techniciensProcessed[] = $technicienId;
            }
            
            // Traiter les véhicules
            $numeroChassis = $data['numero_chassis'];
            if (!empty($numeroChassis) && !in_array($numeroChassis, $vehiculesProcessed)) {
                $vehiculesData[] = [
                    'numero_chassis' => $numeroChassis,
                    'marque' => $this->getMarqueAleatoire(),
                    'modele' => $this->getModeleAleatoire(),
                    'annee' => rand(2015, 2023),
                    'kilometrage' => rand(10000, 150000),
                    'type_carburant' => $this->getCarburantAleatoire(),
                    'date_mise_en_service' => Carbon::now()->subYears(rand(1, 8))->format('Y-m-d'),
                    'statut' => 'actif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $vehiculesProcessed[] = $numeroChassis;
            }
            
            // Traiter les maintenances
            if (!empty($data['Id_maintenance'])) {
                // Convertir la date du format Java vers Carbon
                $dateString = $data['Date_maintenance'];
                $date = $this->parseJavaDate($dateString);
                
                // Normaliser le type d'opération
                $natureOperation = $this->normalizeNatureOperation($data['Nature de l\'operation']);
                
                $maintenancesData[] = [
                    'id_maintenance' => $data['Id_maintenance'],
                    'id_technicien' => $data['Id_Techniscien'],
                    'numero_chassis' => $data['numero_chassis'],
                    'libelle_maintenance' => $data['Libelle_maintenance'],
                    'date_maintenance' => $date,
                    'nature_operation' => $natureOperation,
                    'statut' => 'termine',
                    'cout' => $this->getCoutEstime($data['Libelle_maintenance']),
                    'duree_heures' => $this->getDureeEstimee($data['Libelle_maintenance']),
                    'observations' => $this->generateObservations($data['Libelle_maintenance'], $natureOperation),
                    'created_at' => $date,
                    'updated_at' => now(),
                ];
            }
        }
        
        fclose($file);

        // Insérer les données dans la base
        $this->command->info('Insertion des techniciens...');
        DB::table('techniciens')->insert($techniciensData);
        $this->command->info(count($techniciensData) . ' techniciens insérés.');

        $this->command->info('Insertion des véhicules...');
        DB::table('vehicules')->insert($vehiculesData);
        $this->command->info(count($vehiculesData) . ' véhicules insérés.');

        $this->command->info('Insertion des maintenances...');
        // Insérer par lots de 100 pour éviter les problèmes de mémoire
        $chunks = array_chunk($maintenancesData, 100);
        foreach ($chunks as $chunk) {
            DB::table('maintenances')->insert($chunk);
        }
        $this->command->info(count($maintenancesData) . ' maintenances insérées.');

        // Réactiver les contraintes de clés étrangères
        Schema::enableForeignKeyConstraints();

        $this->command->info('Seeding terminé avec succès!');
    }

    /**
     * Parse une date au format Java vers Carbon
     */
    private function parseJavaDate($dateString)
    {
        // Format: "Fri Feb 02 00:00:00 CET 2024"
        try {
            // Extraire les composants de la date
            $parts = explode(' ', $dateString);
            $month = $this->getMonthNumber($parts[1]);
            $day = $parts[2];
            $year = $parts[5];
            
            return Carbon::createFromFormat('Y-m-d', "$year-$month-$day")->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // En cas d'erreur, retourner la date actuelle
            return now();
        }
    }

    /**
     * Convertir le nom du mois en numéro
     */
    private function getMonthNumber($monthName)
    {
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12'
        ];
        
        return $months[$monthName] ?? '01';
    }

    /**
     * Normaliser le type d'opération
     */
    private function normalizeNatureOperation($nature)
    {
        $nature = trim($nature);
        
        if (stripos($nature, 'Corrective') !== false && stripos($nature, 'Préventive') !== false) {
            return 'mixte';
        } elseif (stripos($nature, 'Corrective') !== false) {
            return 'corrective';
        } elseif (stripos($nature, 'Préventive') !== false) {
            return 'preventive';
        }
        
        return 'preventive'; // Par défaut
    }

    /**
     * Obtenir une spécialité pour un technicien
     */
    private function getSpecialite($technicienId)
    {
        $specialites = [
            'Mécanique générale',
            'Électricité automobile',
            'Pneumatique et suspension',
            'Carrosserie',
            'Diagnostic électronique',
            'Climatisation',
            'Transmission'
        ];
        
        return $specialites[($technicienId - 1) % count($specialites)];
    }

    /**
     * Générer une marque aléatoire
     */
    private function getMarqueAleatoire()
    {
        $marques = ['Toyota', 'Peugeot', 'Renault', 'Nissan', 'Volkswagen', 'Mercedes', 'BMW', 'Hyundai'];
        return $marques[array_rand($marques)];
    }

    /**
     * Générer un modèle aléatoire
     */
    private function getModeleAleatoire()
    {
        $modeles = ['Corolla', '308', 'Clio', 'Qashqai', 'Golf', 'Classe C', 'Série 3', 'Tucson'];
        return $modeles[array_rand($modeles)];
    }

    /**
     * Générer un type de carburant aléatoire
     */
    private function getCarburantAleatoire()
    {
        $carburants = ['essence', 'diesel', 'hybride', 'electrique'];
        return $carburants[array_rand($carburants)];
    }

    /**
     * Estimer le coût basé sur le libellé
     */
    private function getCoutEstime($libelle)
    {
        $libelle = strtolower($libelle);
        
        if (strpos($libelle, 'vidange') !== false) {
            return rand(50, 100);
        } elseif (strpos($libelle, 'amortisseur') !== false || strpos($libelle, 'suspension') !== false) {
            return rand(300, 600);
        } elseif (strpos($libelle, 'frein') !== false) {
            return rand(150, 350);
        } elseif (strpos($libelle, 'pneu') !== false) {
            return rand(200, 400);
        } elseif (strpos($libelle, 'diagnostic') !== false) {
            return rand(50, 150);
        } elseif (strpos($libelle, 'batterie') !== false) {
            return rand(100, 200);
        } elseif (strpos($libelle, 'courroie') !== false) {
            return rand(200, 400);
        }
        
        return rand(100, 300);
    }

    /**
     * Estimer la durée basée sur le libellé
     */
    private function getDureeEstimee($libelle)
    {
        $libelle = strtolower($libelle);
        
        if (strpos($libelle, 'vidange') !== false) {
            return 0.5;
        } elseif (strpos($libelle, 'amortisseur') !== false || strpos($libelle, 'suspension') !== false) {
            return rand(2, 4);
        } elseif (strpos($libelle, 'diagnostic') !== false) {
            return 1;
        }
        
        return rand(1, 3);
    }

    /**
     * Générer des observations
     */
    private function generateObservations($libelle, $nature)
    {
        if ($nature === 'corrective') {
            return "Intervention corrective effectuée. " . $libelle . " - Véhicule remis en état de fonctionnement.";
        } elseif ($nature === 'preventive') {
            return "Maintenance préventive réalisée conformément au planning. " . $libelle;
        }
        
        return "Intervention mixte (préventive et corrective) réalisée. " . $libelle;
    }
}