<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementsExport implements FromCollection, WithHeadings, WithMapping
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
            'Tanggal',
            'Produk',
            'Dari Cabang',
            'Ke Cabang',
            'Jumlah',
            'Tipe',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->created_at->format('d/m/Y H:i'),
            $movement->product->name,
            $movement->sourceBranch->name ?? '-',
            $movement->destinationBranch->name ?? '-',
            $movement->quantity,
            $movement->type->value,
        ];
    }
}