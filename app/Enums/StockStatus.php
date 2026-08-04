<?php

namespace App\Enums;

enum StockStatus: string{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case PartiallyApproved = 'partially_approved';


    public function label(): string{
        return match ($this){
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::PartiallyApproved => 'Partially Approved',
        };
    }
}