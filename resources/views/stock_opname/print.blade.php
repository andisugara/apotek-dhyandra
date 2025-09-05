<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <title>Stock Opname - {{ $stockOpname->kode }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.3;
                margin: 0;
                padding: 10px;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .header h1 {
                font-size: 18px;
                margin: 5px 0;
            }

            .header p {
                font-size: 12px;
                margin: 5px 0;
            }

            .info-table {
                width: 100%;
                margin-bottom: 20px;
                border-collapse: collapse;
            }

            .info-table td,
            .info-table th {
                padding: 5px;
                vertical-align: top;
                text-align: left;
            }

            .details-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .details-table th,
            .details-table td {
                border: 1px solid #000;
                padding: 5px;
                font-size: 10px;
                text-align: left;
            }

            .details-table th {
                background-color: #f2f2f2;
            }

            .footer {
                margin-top: 30px;
                text-align: right;
            }

            .signature-box {
                margin-top: 50px;
            }

            .text-center {
                text-align: center;
            }

            .text-success {
                color: green;
            }

            .text-danger {
                color: red;
            }

            .summary-container {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            }

            .summary-box {
                border: 1px solid #000;
                padding: 10px;
                width: 30%;
            }

            .summary-box h3 {
                margin: 0 0 5px 0;
                font-size: 12px;
            }

            .summary-box .value {
                font-size: 20px;
                font-weight: bold;
            }

            .page-break {
                page-break-after: always;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <h1>LAPORAN STOCK OPNAME</h1>
            <p>APOTEK DHYANDRA</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="120"><strong>Kode</strong></td>
                <td width="10">:</td>
                <td>{{ $stockOpname->kode }}</td>
                <td width="120"><strong>Petugas</strong></td>
                <td width="10">:</td>
                <td>{{ $stockOpname->user->name }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal</strong></td>
                <td>:</td>
                <td>{{ date('d/m/Y', strtotime($stockOpname->tanggal)) }}</td>
                <td><strong>Status</strong></td>
                <td>:</td>
                <td>{{ ucfirst($stockOpname->status) }}</td>
            </tr>
            <tr>
                <td><strong>Keterangan</strong></td>
                <td>:</td>
                <td colspan="4">{{ $stockOpname->keterangan ?? '-' }}</td>
            </tr>
        </table>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="border: 1px solid #000; padding: 10px; width: 30%;">
                <h3 style="margin: 0 0 5px 0;">Item Sesuai</h3>
                <div style="font-size: 16px; font-weight: bold;">
                    {{ $stockOpname->details->where('selisih', 0)->count() }}</div>
                <div>Stok sistem sesuai dengan stok fisik</div>
            </div>

            <div style="border: 1px solid #000; padding: 10px; width: 30%;">
                <h3 style="margin: 0 0 5px 0;">Item Kurang</h3>
                <div style="font-size: 16px; font-weight: bold;">
                    {{ $stockOpname->details->where('selisih', '<', 0)->count() }}</div>
                <div>Stok fisik kurang dari stok sistem</div>
            </div>

            <div style="border: 1px solid #000; padding: 10px; width: 30%;">
                <h3 style="margin: 0 0 5px 0;">Item Lebih</h3>
                <div style="font-size: 16px; font-weight: bold;">
                    {{ $stockOpname->details->where('selisih', '>', 0)->count() }}</div>
                <div>Stok fisik lebih dari stok sistem</div>
            </div>
        </div>

        <h3>Daftar Obat</h3>

        <table class="details-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Obat</th>
                    <th width="10%">Satuan</th>
                    <th width="10%">Lokasi</th>
                    <th width="10%">Batch</th>
                    <th width="10%">Expired</th>
                    <th width="8%">Stok Sistem</th>
                    <th width="8%">Stok Fisik</th>
                    <th width="8%">Selisih</th>
                    <th width="11%">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockOpname->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->obat->nama_obat }}</td>
                        <td>{{ $detail->satuan->nama }}</td>
                        <td>{{ $detail->lokasi->nama }}</td>
                        <td>{{ $detail->no_batch }}</td>
                        <td>{{ date('d/m/Y', strtotime($detail->tanggal_expired)) }}</td>
                        <td>{{ $detail->stok_sistem }}</td>
                        <td>{{ $detail->stok_fisik }}</td>
                        <td>
                            @if ($detail->selisih > 0)
                                <span class="text-success">+{{ $detail->selisih }}</span>
                            @elseif($detail->selisih < 0)
                                <span class="text-danger">{{ $detail->selisih }}</span>
                            @else
                                0
                            @endif
                        </td>
                        <td>{{ $detail->tindakan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div>Dicetak pada: {{ date('d/m/Y H:i:s') }}</div>

            <div class="signature-box">
                <div>Disetujui oleh,</div>
                <br><br><br><br>
                <div>(__________________)</div>
                <div>Apoteker Penanggung Jawab</div>
            </div>
        </div>
    </body>

</html>
