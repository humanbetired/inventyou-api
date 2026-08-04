<?php

namespace App\Enums;

enum StockMovementType: string{
    
    case InitialStock = 'initial_stock';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';

    public function label(): string{

        return match ($this){
            self::InitialStock => 'Initial Stock',
            self::Transfer => 'Transfer',
            self::Adjustment => 'Adjustment',
        };
    }
}