<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requesting_branch_id',
        'requested_by_user_id',
        'approved_by_user_id',
        'status',
        'rejection_reason',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => StockStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function requestingBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requesting_branch_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockRequestItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}