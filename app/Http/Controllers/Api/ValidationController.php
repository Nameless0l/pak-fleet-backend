<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateOperationRequest;
use App\Models\MaintenanceOperation;
use Illuminate\Http\Request;
use App\Notifications\OperationValidated;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ValidationController extends Controller
{
    use AuthorizesRequests;
    public function pendingOperations(Request $request)
    {

        $operations = MaintenanceOperation::pending()
            ->with(['vehicle', 'maintenanceType', 'technician'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json($operations);
    }

    public function validate(ValidateOperationRequest $request, MaintenanceOperation $operation)
    {

        $operation->update([
            'status' => $request->status,
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
            'validation_comment' => $request->comment,
        ]);

        // Notification au technicien
        $operation->technician->notify(new OperationValidated($operation));

        return response()->json([
            'message' => 'Opération ' . ($request->status === 'validated' ? 'validée' : 'rejetée') . ' avec succès',
            'operation' => $operation->load(['validator'])
        ]);
    }
}
