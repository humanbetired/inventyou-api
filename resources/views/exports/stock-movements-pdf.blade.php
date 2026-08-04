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
    </style>
</head>
<body>
    <h1>Laporan Riwayat Pergerakan Stok</h1>
    <p class="subtitle">Dicetak pada {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Produk</th>
                <th>Dari Cabang</th>
                <th>Ke Cabang</th>
                <th>Jumlah</th>
                <th>Tipe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $movement->product->name }}</td>
                    <td>{{ $movement->sourceBranch->name ?? '-' }}</td>
                    <td>{{ $movement->destinationBranch->name ?? '-' }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->type->value }}</td>
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