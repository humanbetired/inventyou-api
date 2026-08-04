<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_central_warehouse',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'is_central_warehouse' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockRequests(): HasMany
    {
        return $this->hasMany(StockRequest::class, 'requesting_branch_id');
    }
}