<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exports\ProductStocksExport;
use App\Models\ProductStock;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\AddInitialStockRequest;
use App\Services\StockRequestService;

class ProductStockController extends Controller
{

    public function __construct(
        protected StockRequestService $stockRequestService
    ) {}

    public function byBranch(Request $request, Branch $branch)
    {
        $user = $request->user();

        $isPrivilegedRole = in_array($user->role, [UserRole::SuperAdmin, UserRole::WarehouseAdmin], strict: true);

        if (! $isPrivilegedRole && $user->branch_id !== $branch->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat stok cabang ini.',
            ], 403);
        }

        if ($user->role === UserRole::WarehouseAdmin && ! $user->branch->is_central_warehouse && $user->branch_id !== $branch->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat stok cabang ini.',
            ], 403);
        }

        return response()->json([
            'data' => $branch->productStocks()->with('product')->get(),
        ]);
    }

    public function byProduct(Request $request, Product $product)
    {
        $user = $request->user();

        $isPrivilegedRole = in_array($user->role, [UserRole::SuperAdmin], strict: true)
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isPrivilegedRole) {
            return response()->json([
                'message' => 'Hanya Super Admin atau Warehouse Admin pusat yang bisa melihat sebaran stok semua cabang.',
            ], 403);
        }

        return response()->json([
            'data' => $product->stocks()->with('branch')->get(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->buildAccessibleQuery($request);

        return Excel::download(new ProductStocksExport($query), 'product-stocks.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $stocks = $this->buildAccessibleQuery($request)->get();

        $pdf = Pdf::loadView('exports.product-stocks-pdf', ['stocks' => $stocks]);

        return $pdf->download('product-stocks.pdf');
    }

    protected function buildAccessibleQuery(Request $request)
    {
        $user = $request->user();

        $query = ProductStock::with(['product', 'branch']);

        $isPrivileged = $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isPrivileged) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        return response()->json([
            'data' => $this->buildAccessibleQuery($request)->get(),
        ]);
    }

    public function store(AddInitialStockRequest $request)
    {
        $user = $request->user();

        $isAuthorized = $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isAuthorized) {
            return response()->json([
                'message' => 'Hanya Super Admin atau Warehouse Admin pusat yang bisa menambah stok manual.',
            ], 403);
        }

        $this->stockRequestService->addInitialStock(
            $request->validated('product_id'),
            $request->validated('branch_id'),
            $request->validated('quantity')
        );

        return response()->json([
            'message' => 'Stok berhasil ditambahkan.',
        ], 201);
    }
}