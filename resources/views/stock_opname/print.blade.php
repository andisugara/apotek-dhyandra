<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stock Opname Report - {{ $stockOpname->kode }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                font-size: 12px;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .header h1 {
                margin: 0;
                padding: 0;
                font-size: 18px;
            }

            .header p {
                margin: 5px 0 0;
                padding: 0;
            }

            .info-container {
                margin-bottom: 20px;
            }

            .info-row {
                display: flex;
                margin-bottom: 10px;
            }

            .info-row .label {
                font-weight: bold;
                width: 150px;
            }

            .info-row .value {
                flex-grow: 1;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            table,
            th,
            td {
                border: 1px solid #000;
            }

            th,
            td {
                padding: 5px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2;
            }

            .footer {
                margin-top: 30px;
            }

            .signature {
                float: right;
                width: 200px;
                text-align: center;
            }

            .signature .line {
                margin-top: 50px;
                border-bottom: 1px solid #000;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <h1>LAPORAN STOCK OPNAME</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <div class="info-container">
            <div class="info-row">
                <div class="label">Kode Stock Opname:</div>
                <div class="value">{{ $stockOpname->kode }}</div>
            </div>
            <div class="info-row">
                <div class="label">Tanggal:</div>
                <div class="value">{{ $stockOpname->tanggal->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="label">Status:</div>
                <div class="value">{{ ucfirst($stockOpname->status) }}</div>
            </div>
            <div class="info-row">
                <div class="label">Petugas:</div>
                <div class="value">{{ $stockOpname->user->name }}</div>
            </div>
            <div class="info-row">
                <div class="label">Keterangan:</div>
                <div class="value">{{ $stockOpname->keterangan ?: '-' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Obat</th>
                    <th>Satuan</th>
                    <th>Lokasi</th>
                    <th>Stok Sistem</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockOpname->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->obat->nama_obat }}</td>
                        <td>{{ $detail->satuan->nama }}</td>
                        <td>{{ $detail->lokasi->nama }}</td>
                        <td>{{ $detail->stok_sistem }}</td>
                        <td>{{ $detail->stok_fisik }}</td>
                        <td>{{ $detail->selisih > 0 ? '+' . $detail->selisih : $detail->selisih }}</td>
                        <td>{{ $detail->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">Tidak ada data obat</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div class="signature">
                <p>{{ now()->format('d/m/Y') }}</p>
                <p>Petugas</p>
                <div class="line"></div>
                <p>{{ $stockOpname->user->name }}</p>
            </div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>

</html>
