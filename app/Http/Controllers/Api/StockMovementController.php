<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Exports\StockMovementsExport;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => $this->buildFilteredQuery($request)->latest()->paginate(20),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->buildFilteredQuery($request)->latest();

        return Excel::download(new StockMovementsExport($query), 'stock-movements.xlsx');
    }

    protected function buildFilteredQuery(Request $request)
    {
        $user = $request->user();

        $query = StockMovement::with(['product', 'sourceBranch', 'destinationBranch']);

        $isPrivileged = $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isPrivileged) {
            $query->where(function ($q) use ($user) {
                $q->where('source_branch_id', $user->branch_id)
                    ->orWhere('destination_branch_id', $user->branch_id);
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('branch_id')) {
            $branchId = $request->input('branch_id');
            $query->where(function ($q) use ($branchId) {
                $q->where('source_branch_id', $branchId)
                    ->orWhere('destination_branch_id', $branchId);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query;
    }


    public function exportPdf(Request $request)
    {
        $movements = $this->buildFilteredQuery($request)->latest()->get();

        $pdf = Pdf::loadView('exports.stock-movements-pdf', ['movements' => $movements]);

        return $pdf->download('stock-movements.pdf');
    }
}