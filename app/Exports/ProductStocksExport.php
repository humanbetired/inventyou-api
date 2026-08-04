<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductStocksExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected $query)
    {
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Produk',
            'Cabang',
            'Jumlah Stok',
            'Ambang Batas Rendah',
            'Status',
        ];
    }

    public function map($stock): array
    {
        $isLow = $stock->quantity <= $stock->product->low_stock_threshold;

        return [
            $stock->product->sku,
            $stock->product->name,
            $stock->branch->name,
            $stock->quantity,
            $stock->product->low_stock_threshold,
            $isLow ? 'Stok Rendah' : 'Normal',
        ];
    }
}