<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockRequest;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->role === UserRole::SuperAdmin => response()->json(['data' => $this->superAdminSummary()]),
            $user->role === UserRole::WarehouseAdmin => response()->json(['data' => $this->warehouseAdminSummary($user)]),
            default => response()->json(['data' => $this->staffSummary($user)]),
        };
    }

    protected function superAdminSummary(): array
    {
        return [
            'total_branches' => Branch::count(),
            'total_products' => Product::count(),
            'stock_requests_by_status' => StockRequest::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
            'low_stock_alerts' => $this->lowStockAlerts(),
        ];
    }

    protected function warehouseAdminSummary($user): array
    {
        $branch = $user->branch;

        $query = StockRequest::query();

        if (! $branch->is_central_warehouse) {
            $query->where('requesting_branch_id', $branch->id);
        }

        return [
            'branch_name' => $branch->name,
            'is_central_warehouse' => $branch->is_central_warehouse,
            'stock_requests_by_status' => (clone $query)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
            'pending_requests_count' => (clone $query)->where('status', StockStatus::Pending)->count(),
            'low_stock_alerts' => $this->lowStockAlerts($branch->is_central_warehouse ? null : $branch->id),
        ];
    }

    protected function staffSummary($user): array
    {
        $myRequests = StockRequest::where('requested_by_user_id', $user->id);

        return [
            'branch_name' => $user->branch->name,
            'my_requests_by_status' => (clone $myRequests)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
            'my_pending_requests_count' => (clone $myRequests)->where('status', StockStatus::Pending)->count(),
        ];
    }

    protected function lowStockAlerts(?int $branchId = null)
    {
        $query = ProductStock::with(['product', 'branch'])
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->whereColumn('product_stocks.quantity', '<=', 'products.low_stock_threshold')
            ->select('product_stocks.*');

        if ($branchId) {
            $query->where('product_stocks.branch_id', $branchId);
        }

        return $query->get();
    }

    public function stockTrend(Request $request)
    {
        $user = $request->user();

        $query = StockMovement::query();

        $isPrivileged = $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isPrivileged) {
            $query->where(function ($q) use ($user) {
                $q->where('source_branch_id', $user->branch_id)
                    ->orWhere('destination_branch_id', $user->branch_id);
            });
        }

        $startDate = now()->subDays(29)->startOfDay();

        $movements = $query
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, type, SUM(quantity) as total_quantity')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $days = collect(range(0, 29))->map(function ($daysAgo) {
            return now()->subDays(29 - $daysAgo)->format('Y-m-d');
        });

        $trend = $days->map(function ($date) use ($movements) {
            $dayData = $movements->filter(fn ($m) => $m->date === $date);

            return [
                'date' => $date,
                'transfer' => (int) ($dayData->firstWhere('type', 'transfer')->total_quantity ?? 0),
                'initial_stock' => (int) ($dayData->firstWhere('type', 'initial_stock')->total_quantity ?? 0),
                'adjustment' => (int) ($dayData->firstWhere('type', 'adjustment')->total_quantity ?? 0),
            ];
        });

        return response()->json([
            'data' => $trend,
        ]);
    }
}