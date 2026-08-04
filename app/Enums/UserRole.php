<?php

namespace App\Enums;


enum UserRole: string{
    case SuperAdmin = 'super_admin';
    case WarehouseAdmin = 'warehouse_admin';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => "Super Admin",
            self::WarehouseAdmin => "Warehouse Admin",
            self::Staff => "Staff",
        };
    }
}