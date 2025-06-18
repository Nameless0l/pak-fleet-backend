<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de Maintenance {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }
        
        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section h2 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        
        .section h3 {
            color: #34495e;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .stat-card .label {
            font-weight: bold;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 18px;
            color: #3498db;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table tr:hover {
            background-color: #f5f5f5;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background-color: #27ae60;
            color: white;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .badge-info {
            background-color: #3498db;
            color: white;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 20px;
        }
        
        .currency {
            color: #27ae60;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Maintenance</h1>
        <div class="subtitle">
            Année {{ $year }} - Type: {{ ucfirst($type) }}
            <br>
            Généré le {{ $generated_at }}
        </div>
    </div>

    @if($type === 'summary' && isset($stats))
        <div class="section">
            <h2>Statistiques Générales</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="label">Total Véhicules</span>
                    <span class="value">{{ $stats['total_vehicles'] ?? 0 }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">Véhicules Actifs</span>
                    <span class="value">{{ $stats['active_vehicles'] ?? 0 }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">Total Opérations</span>
                    <span class="value">{{ $stats['total_operations'] ?? 0 }}</span>
                </div>
                <div class="stat-card">
                    <span class="label">Coût Total</span>
                    <span class="value currency">{{ number_format($stats['total_cost'] ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="stat-card">
                    <span class="label">Coût Moyen/Opération</span>
                    <span class="value currency">{{ number_format($stats['average_cost_per_operation'] ?? 0, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

        @if(isset($monthly_costs) && count($monthly_costs) > 0)
            <div class="section">
                <h2>Évolution Mensuelle</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Mois</th>
                            <th class="text-center">Nb Opérations</th>
                            <th class="text-center">Coût Main d'œuvre</th>
                            <th class="text-center">Coût Pièces</th>
                            <th class="text-center">Coût Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly_costs as $month)
                            <tr>
                                <td>{{ $month['month'] }}</td>
                                <td class="text-center">{{ $month['operations_count'] }}</td>
                                <td class="text-right currency">{{ number_format($month['labor_cost'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($month['parts_cost'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($month['total_cost'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(isset($costs_by_category) && count($costs_by_category) > 0)
            <div class="section">
                <h2>🔧 Coûts par Catégorie</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-center">Nb Opérations</th>
                            <th class="text-center">Coût Moyen</th>
                            <th class="text-center">Coût Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costs_by_category as $category)
                            <tr>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($category->category) }}</span>
                                </td>
                                <td class="text-center">{{ $category->operations_count }}</td>
                                <td class="text-right currency">{{ number_format($category->average_cost, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($category->total_cost, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(isset($costs_by_vehicle_type) && count($costs_by_vehicle_type) > 0)
            <div class="section">
                <h2>🚗 Coûts par Type de Véhicule</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Type de Véhicule</th>
                            <th class="text-center">Nb Opérations</th>
                            <th class="text-center">Coût Moyen</th>
                            <th class="text-center">Coût Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costs_by_vehicle_type as $vehicleType)
                            <tr>
                                <td>{{ $vehicleType->vehicle_type }}</td>
                                <td class="text-center">{{ $vehicleType->operations_count }}</td>
                                <td class="text-right currency">{{ number_format($vehicleType->average_cost, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($vehicleType->total_cost, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(isset($top_vehicles_by_cost) && count($top_vehicles_by_cost) > 0)
            <div class="section page-break">
                <h2>🏆 Top 10 - Véhicules par Coût</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Immatriculation</th>
                            <th>Marque</th>
                            <th>Modèle</th>
                            <th class="text-center">Nb Opérations</th>
                            <th class="text-center">Coût Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($top_vehicles_by_cost as $vehicle)
                            <tr>
                                <td><strong>{{ $vehicle->registration_number }}</strong></td>
                                <td>{{ $vehicle->brand }}</td>
                                <td>{{ $vehicle->model }}</td>
                                <td class="text-center">{{ $vehicle->operations_count }}</td>
                                <td class="text-right currency">{{ number_format($vehicle->total_cost, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(isset($spare_parts_consumption) && count($spare_parts_consumption) > 0)
            <div class="section">
                <h2>🔩 Consommation Pièces Détachées</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-center">Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spare_parts_consumption as $part)
                            <tr>
                                <td><strong>{{ $part->code }}</strong></td>
                                <td>{{ $part->name }}</td>
                                <td>
                                    <span class="badge badge-warning">{{ ucfirst($part->category) }}</span>
                                </td>
                                <td class="text-center">{{ $part->total_quantity }}</td>
                                <td class="text-right currency">{{ number_format($part->total_value, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(isset($trends))
            <div class="section">
                <h2>📊 Tendances (Comparaison Année Précédente)</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="label">Évolution Coûts</span>
                        <span class="value {{ $trends['cost_evolution']['variation'] >= 0 ? 'currency' : 'text-danger' }}">
                            {{ $trends['cost_evolution']['variation'] > 0 ? '+' : '' }}{{ number_format($trends['cost_evolution']['variation'], 1) }}%
                        </span>
                    </div>
                    <div class="stat-card">
                        <span class="label">Évolution Opérations</span>
                        <span class="value {{ $trends['operations_evolution']['variation'] >= 0 ? 'currency' : 'text-danger' }}">
                            {{ $trends['operations_evolution']['variation'] > 0 ? '+' : '' }}{{ number_format($trends['operations_evolution']['variation'], 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if($type === 'detailed' && isset($operations))
        <div class="section">
            <h2>📋 Opérations Détaillées</h2>
            @if(count($operations) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Véhicule</th>
                            <th>Type Maintenance</th>
                            <th>Technicien</th>
                            <th class="text-center">Coût M.O.</th>
                            <th class="text-center">Coût Pièces</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($operations as $operation)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($operation->operation_date)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $operation->vehicle->registration_number }}</strong>
                                    <br>
                                    <small>{{ $operation->vehicle->brand }} {{ $operation->vehicle->model }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $operation->maintenanceType->name }}</span>
                                </td>
                                <td>{{ $operation->technician->name ?? 'N/A' }}</td>
                                <td class="text-right currency">{{ number_format($operation->labor_cost, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($operation->parts_cost, 0, ',', ' ') }} FCFA</td>
                                <td class="text-right currency">{{ number_format($operation->total_cost, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucune opération trouvée pour cette période</div>
            @endif
        </div>
    @endif

    @if($type === 'vehicles' && isset($vehicles_report))
        <div class="section">
            <h2>🚗 Rapport par Véhicule</h2>
            @if(count($vehicles_report) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Immatriculation</th>
                            <th>Type</th>
                            <th>Marque/Modèle</th>
                            <th class="text-center">Nb Opérations</th>
                            <th class="text-center">Coût Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles_report as $vehicle)
                            <tr>
                                <td><strong>{{ $vehicle->registration_number }}</strong></td>
                                <td>{{ $vehicle->vehicleType->name ?? 'N/A' }}</td>
                                <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td class="text-center">{{ $vehicle->maintenance_operations_count }}</td>
                                <td class="text-right currency">{{ number_format($vehicle->maintenance_operations_sum_total_cost ?? 0, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucun véhicule trouvé</div>
            @endif
        </div>
    @endif

    @if($type === 'spare_parts' && isset($spare_parts_report))
        <div class="section">
            <h2>🔩 Rapport Pièces Détachées</h2>
            @if(count($spare_parts_report) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th class="text-center">Stock Actuel</th>
                            <th class="text-center">Quantité Utilisée</th>
                            <th class="text-center">Valeur Totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spare_parts_report as $part)
                            <tr>
                                <td><strong>{{ $part->code }}</strong></td>
                                <td>{{ $part->name }}</td>
                                <td>
                                    <span class="badge badge-warning">{{ ucfirst($part->category) }}</span>
                                </td>
                                <td class="text-center">{{ $part->quantity_in_stock }}</td>
                                <td class="text-center">{{ $part->total_used }}</td>
                                <td class="text-right currency">{{ number_format($part->total_value, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucune pièce détachée trouvée</div>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Rapport généré automatiquement par le système de gestion de maintenance</p>
        <p>Document confidentiel - Usage interne uniquement</p>
    </div>
</body>
</html>