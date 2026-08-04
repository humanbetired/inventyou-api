<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.subtitle { color: #666; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .low-stock { color: #d97706; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Laporan Stok Produk</h1>
    <p class="subtitle">Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Produk</th>
                <th>Cabang</th>
                <th>Jumlah Stok</th>
                <th>Ambang Batas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stocks as $stock)
                @php $isLow = $stock->quantity <= $stock->product->low_stock_threshold; @endphp
                <tr>
                    <td>{{ $stock->product->sku }}</td>
                    <td>{{ $stock->product->name }}</td>
                    <td>{{ $stock->branch->name }}</td>
                    <td>{{ $stock->quantity }}</td>
                    <td>{{ $stock->product->low_stock_threshold }}</td>
                    <td class="{{ $isLow ? 'low-stock' : '' }}">{{ $isLow ? 'Stok Rendah' : 'Normal' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>