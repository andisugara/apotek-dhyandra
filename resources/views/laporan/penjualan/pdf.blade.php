<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Laporan Penjualan</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            h1 {
                text-align: center;
                font-size: 16px;
                margin-bottom: 5px;
            }

            h2 {
                text-align: center;
                font-size: 14px;
                margin-bottom: 20px;
                font-weight: normal;
            }

            .header {
                margin-bottom: 20px;
                text-align: center;
            }

            .company {
                font-size: 16px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .address {
                margin-bottom: 20px;
                font-size: 11px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 5px;
                font-size: 10px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .text-end {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .total-row {
                font-weight: bold;
            }

            .text-success {
                color: #28a745;
            }

            .text-danger {
                color: #dc3545;
            }

            .summary {
                margin-top: 20px;
                margin-bottom: 20px;
            }

            .summary table {
                width: 350px;
                float: right;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <div class="company">APOTEK DHYANDRA</div>
            <div class="address">
                Jl. Raya Contoh No. 123, Kota Contoh<br>
                Telp: (021) 1234567 | Email: info@apotekdhyandra.com
            </div>
        </div>

        <h1>LAPORAN PENJUALAN</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Faktur</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Satuan</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Diskon</th>
                    <th class="text-end">PPN</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesData as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->no_faktur }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_penjualan)->format('d/m/Y H:i') }}</td>
                        <td>{{ $item->nama_obat }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td class="text-end">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-end">{{ $item->jumlah }}</td>
                        <td class="text-end">{{ number_format($item->diskon, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->ppn, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="text-end {{ $item->keuntungan >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($item->keuntungan, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary">
            <table>
                <tr>
                    <td>Total Penjualan</td>
                    <td class="text-end">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Harga Pokok Penjualan (HPP)</td>
                    <td class="text-end">Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Keuntungan</td>
                    <td class="text-end {{ $summary['total_keuntungan'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($summary['total_keuntungan'], 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>Margin Keuntungan</td>
                    <td class="text-end {{ $summary['total_keuntungan'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $summary['total_penjualan'] > 0 ? number_format(($summary['total_keuntungan'] / $summary['total_penjualan']) * 100, 2) : 0 }}%
                    </td>
                </tr>
            </table>
            <div style="clear: both;"></div>
        </div>

        <div style="text-align: right; margin-top: 50px; font-size: 11px;">
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
            <br><br><br>
            <p>_________________________</p>
            <p>Apoteker Penanggung Jawab</p>
        </div>
    </body>

</html>
