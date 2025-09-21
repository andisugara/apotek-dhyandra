<!DOCTYPE html>
<html>

    <head>
        <title>{{ $title }}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            h1 {
                font-size: 16px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 20px;
            }

            .header {
                margin-bottom: 20px;
            }

            .header .title {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
            }

            .header .subtitle {
                text-align: center;
                font-size: 14px;
            }

            .header .date {
                text-align: center;
                font-size: 12px;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            table,
            th,
            td {
                border: 1px solid black;
            }

            th {
                background-color: #f2f2f2;
                font-weight: bold;
                padding: 5px;
                text-align: center;
                font-size: 11px;
            }

            td {
                padding: 5px;
                font-size: 10px;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .expired {
                color: red;
                font-weight: bold;
            }

            .soon-expired {
                color: orange;
                font-weight: bold;
            }

            .footer {
                margin-top: 20px;
                font-size: 10px;
                text-align: right;
            }

            .footer .signature {
                margin-top: 60px;
            }
        </style>
    </head>

    <body>
        <div class="header">
            <div class="title">{{ config('app.name', 'Apotek') }}</div>
            <div class="subtitle">{{ $title }}</div>
            <div class="date">Tanggal Cetak: {{ $today->format('d/m/Y') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Nama Obat</th>
                    <th width="8%">Kode</th>
                    <th width="8%">Satuan</th>
                    <th width="10%">Golongan</th>
                    <th width="10%">Kategori</th>
                    <th width="8%">Batch</th>
                    <th width="10%">Tgl Expired</th>
                    <th width="8%">Qty</th>
                    <th width="10%">Status</th>
                    <th width="8%">Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @if (count($data) > 0)
                    @foreach ($data as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $item->obat ? $item->obat->nama_obat : '-' }}</td>
                            <td>{{ $item->obat ? $item->obat->kode_obat : '-' }}</td>
                            <td>{{ $item->obatSatuan && $item->obatSatuan->satuan ? $item->obatSatuan->satuan->nama : ($item->satuan ? $item->satuan->nama : '-') }}
                            </td>
                            <td>{{ $item->obat && $item->obat->golongan ? $item->obat->golongan->nama : '-' }}</td>
                            <td>{{ $item->obat && $item->obat->kategori ? $item->obat->kategori->nama : '-' }}</td>
                            <td>{{ $item->no_batch }}</td>
                            <td>
                                @if ($item->tanggal_expired)
                                    {{ $item->tanggal_expired->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->qty }}</td>
                            <td>
                                @php
                                    $status = '';
                                    $class = '';

                                    if ($item->tanggal_expired < $today) {
                                        $status = 'Expired';
                                        $class = 'expired';
                                    } else {
                                        $daysLeft = $today->diffInDays($item->tanggal_expired, false);
                                        if ($daysLeft <= 30) {
                                            $status = 'Segera Expired (' . $daysLeft . ' hari)';
                                            $class = 'soon-expired';
                                        } else {
                                            $status = 'Baik';
                                        }
                                    }
                                @endphp
                                <span class="{{ $class }}">{{ $status }}</span>
                            </td>
                            <td>{{ $item->lokasi ? $item->lokasi->nama : '-' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" style="text-align: center;">Tidak ada data</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <div class="signature">
                <p>Penanggung Jawab</p>
                <br>
                <br>
                <p>(__________________)</p>
            </div>
        </div>
    </body>

</html>
