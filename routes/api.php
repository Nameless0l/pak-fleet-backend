<?php
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\MaintenanceOperationController;
use App\Http\Controllers\Api\SparePartController;
use App\Http\Controllers\Api\ValidationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

// Routes protégées
Route::middleware(['auth:sanctum'])->group(function () {
    // Profil utilisateur
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Véhicules
    Route::apiResource('vehicles', VehicleController::class);
    Route::get('/vehicle-types', function () {
        return \App\Models\VehicleType::all();
    });

    // Types de maintenance
    Route::get('/maintenance-types', function () {
        return \App\Models\MaintenanceType::all();
    });

    // Opérations de maintenance
    Route::get('maintenance-operations/planned', [MaintenanceOperationController::class, 'plannedOperations']);

    Route::apiResource('maintenance-operations', MaintenanceOperationController::class);
    // Pièces détachées
    Route::apiResource('spare-parts', SparePartController::class);
    Route::post('/spare-parts/{sparePart}/update-stock', [SparePartController::class, 'updateStock']);
    Route::get('/spare-parts/alerts/low-stock', [SparePartController::class, 'lowStockAlert']);

    // Routes spécifiques au Chef de Service
    Route::middleware(['role:chief'])->group(function () {
        // Validation des opérations
        Route::get('/validations/pending', [ValidationController::class, 'pendingOperations']);
        Route::post('/maintenance-operations/{operation}/validate', [ValidationController::class, 'validate']);

        // Gestion des utilisateurs
        Route::get('/users', function (Request $request) {
            return \App\Models\User::where('role', 'technician')
                ->when($request->search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->paginate(15);
        });
        Route::post('/users', function (Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'employee_id' => 'required|string|unique:users',
                'role' => 'required|in:chief,technician',
            ]);

            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'employee_id' => $validated['employee_id'],
                'role' => $validated['role'],
            ]);

            return response()->json($user, 201);
        });
        Route::put('/users/{user}', function (Request $request, \App\Models\User $user) {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'is_active' => 'sometimes|boolean',
            ]);

            $user->update($validated);

            return response()->json($user);
        });

        // Rapports
        Route::get('/reports/export', [ReportController::class, 'export']);
        Route::get('/reports/annual-summary/{year}', [ReportController::class, 'annualSummary']);
    });
});
