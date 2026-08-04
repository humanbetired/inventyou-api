<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\StockStatus;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockRequest;
use App\Models\StockRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockRequestService
{
    public function createRequest(User $staff, array $items): StockRequest
    {
        return DB::transaction(function () use ($staff, $items) {
            $centralWarehouse = $staff->branch;

            $stockRequest = StockRequest::create([
                'requesting_branch_id' => $staff->branch_id,
                'requested_by_user_id' => $staff->id,
                'status' => StockStatus::Pending,
            ]);

            foreach ($items as $item) {
                $stockRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'status' => StockStatus::Pending,
                ]);
            }

            return $stockRequest->load('items.product');
        });
    }

    public function processApproval(StockRequest $stockRequest, User $approver, array $items): StockRequest
    {
        foreach ($items as $itemData) {
            $this->processSingleItem($stockRequest, $approver, $itemData);
        }

        $this->recalculateHeaderStatus($stockRequest);

        return $stockRequest->fresh(['items.product', 'items.sourceBranch', 'requestingBranch']);
    }

    protected function processSingleItem(StockRequest $stockRequest, User $approver, array $itemData): void
    {
        DB::transaction(function () use ($stockRequest, $approver, $itemData) {
            $item = StockRequestItem::lockForUpdate()->findOrFail($itemData['stock_request_item_id']);

            if ($item->status !== StockStatus::Pending) {
                return;
            }

            if ($itemData['decision'] === 'reject') {
                $item->update(['status' => StockStatus::Rejected]);

                return;
            }

            $quantityApproved = $itemData['quantity_approved'];

            if ($quantityApproved > $item->quantity_requested) {
                $item->update(['status' => StockStatus::Rejected]);

                return;
            }

            $sourceStock = ProductStock::where('product_id', $item->product_id)
                ->where('branch_id', $itemData['source_branch_id'])
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $quantityApproved) {
                $item->update(['status' => StockStatus::Rejected]);

                return;
            }

            $sourceStock->decrement('quantity', $quantityApproved);

            ProductStock::firstOrCreate(
                [
                    'product_id' => $item->product_id,
                    'branch_id' => $stockRequest->requesting_branch_id,
                ],
                ['quantity' => 0]
            );

            ProductStock::where('product_id', $item->product_id)
                ->where('branch_id', $stockRequest->requesting_branch_id)
                ->lockForUpdate()
                ->increment('quantity', $quantityApproved);

            StockMovement::create([
                'product_id' => $item->product_id,
                'source_branch_id' => $itemData['source_branch_id'],
                'destination_branch_id' => $stockRequest->requesting_branch_id,
                'quantity' => $quantityApproved,
                'type' => StockMovementType::Transfer,
                'stock_request_id' => $stockRequest->id,
            ]);

            $item->update([
                'status' => StockStatus::Approved,
                'source_branch_id' => $itemData['source_branch_id'],
                'quantity_approved' => $quantityApproved,
            ]);
        });
    }

    protected function recalculateHeaderStatus(StockRequest $stockRequest): void
    {
        $items = $stockRequest->items()->get();

        $allApproved = $items->every(fn ($item) => $item->status === StockStatus::Approved);
        $allRejected = $items->every(fn ($item) => $item->status === StockStatus::Rejected);
        $stillPending = $items->contains(fn ($item) => $item->status === StockStatus::Pending);

        $newStatus = match (true) {
            $stillPending => StockStatus::Pending,
            $allApproved => StockStatus::Approved,
            $allRejected => StockStatus::Rejected,
            default => StockStatus::PartiallyApproved,
        };

        $stockRequest->update([
            'status' => $newStatus,
            'approved_by_user_id' => $stockRequest->approved_by_user_id ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    public function addInitialStock(int $productId, int $branchId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $branchId, $quantity) {
            ProductStock::firstOrCreate(
                ['product_id' => $productId, 'branch_id' => $branchId],
                ['quantity' => 0]
            );

            ProductStock::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $productId,
                'source_branch_id' => null,
                'destination_branch_id' => $branchId,
                'quantity' => $quantity,
                'type' => StockMovementType::InitialStock,
                'stock_request_id' => null,
            ]);
        });
    }
}