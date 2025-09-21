<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type"
            content="text/html        <h1>LAPORAN PEMBELIAN</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @if (isset($jenisPembayaran) && $jenisPembayaran)
<br>Jenis Pembayaran: {{ $jenisPembayaran }}
@endif
            @if (isset($supplierId) && $supplierId)
<br>Supplier: {{ App\Models\Supplier::find($supplierId)->nama ?? 'Unknown' }}
@endif
            @if (isset($obatId) && $obatId)
<br>Obat: {{ App\Models\Obat::find($obatId)->nama_obat ?? 'Unknown' }}
@endif
        </h2>et=utf-8" />
        <title>Laporan Pembelian</title>
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

        <h1>LAPORAN PEMBELIAN</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            @if (isset($jenisPembayaran) && $jenisPembayaran)
                <br>Jenis Pembayaran: {{ $jenisPembayaran }}
            @endif
        </h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Faktur</th>
                    <th>No. PO</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Jenis</th>
                    <th>Obat</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th class="text-end">Harga Beli</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Diskon %</th>
                    <th class="text-end">Diskon Rp</th>
                    <th class="text-end">HPP</th>
                    <th class="text-end">Margin %</th>
                    <th>Batch</th>
                    <th>Exp. Date</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseData as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->no_faktur }}</td>
                        <td>{{ $item->no_po ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_faktur)->format('d/m/Y') }}</td>
                        <td>{{ $item->nama_supplier }}</td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->nama_obat }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td class="text-end">{{ $item->jumlah }}</td>
                        <td class="text-end">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->diskon_persen, 2, ',', '.') }}%</td>
                        <td class="text-end">{{ number_format($item->diskon_nominal, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->hpp_per_unit, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->margin_jual_persen, 2, ',', '.') }}%</td>
                        <td>{{ $item->no_batch }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_expired)->format('d/m/Y') }}</td>
                        <td class="text-end">{{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="18" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary">
            <table>
                <tr>
                    <td>Total Pembelian</td>
                    <td class="text-end">Rp {{ number_format($summary['total_pembelian'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Diskon</td>
                    <td class="text-end">Rp {{ number_format($summary['total_diskon'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Faktur</td>
                    <td class="text-end">{{ number_format($summary['total_faktur'], 0, ',', '.') }}</td>
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
