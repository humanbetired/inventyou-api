<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveStockRequestRequest;
use App\Http\Requests\StoreStockRequestRequest;
use App\Models\StockRequest;
use App\Services\StockRequestService;
use Illuminate\Http\Request;

class StockRequestController extends Controller
{
    public function __construct(
        protected StockRequestService $stockRequestService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = StockRequest::with(['requestingBranch', 'requestedBy', 'items.product']);

        if ($user->role === UserRole::Staff) {
            $query->where('requested_by_user_id', $user->id);
        } elseif ($user->role === UserRole::WarehouseAdmin && ! $user->branch->is_central_warehouse) {
            $query->where('requesting_branch_id', $user->branch_id);
        }

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function show(Request $request, StockRequest $stockRequest)
    {
        $user = $request->user();

        $isOwner = $stockRequest->requested_by_user_id === $user->id;
        $isSameBranch = $stockRequest->requesting_branch_id === $user->branch_id;
        $isPrivileged = $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse);

        if (! $isOwner && ! $isSameBranch && ! $isPrivileged) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk melihat pengajuan ini.',
            ], 403);
        }

        return response()->json([
            'data' => $stockRequest->load(['requestingBranch', 'requestedBy', 'approvedBy', 'items.product', 'items.sourceBranch']),
        ]);
    }

    public function store(StoreStockRequestRequest $request)
    {
        $stockRequest = $this->stockRequestService->createRequest(
            $request->user(),
            $request->validated('items')
        );

        return response()->json([
            'data' => $stockRequest,
        ], 201);
    }

    public function approve(ApproveStockRequestRequest $request, StockRequest $stockRequest)
    {
        $user = $request->user();

        if ($user->role !== UserRole::SuperAdmin && ! ($user->role === UserRole::WarehouseAdmin && $user->branch->is_central_warehouse)) {
            return response()->json([
                'message' => 'Hanya Warehouse Admin pusat atau Super Admin yang bisa memproses pengajuan.',
            ], 403);
        }

        $updatedRequest = $this->stockRequestService->processApproval(
            $stockRequest,
            $user,
            $request->validated('items')
        );

        return response()->json([
            'data' => $updatedRequest,
        ]);
    }
}