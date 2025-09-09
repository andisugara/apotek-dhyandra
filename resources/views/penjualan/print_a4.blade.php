<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Faktur Penjualan - {{ $penjualan->no_faktur }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                font-size: 12pt;
            }

            .faktur {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
            }

            .header {
                width: 100%;
                display: flex;
                margin-bottom: 20px;
            }

            .logo-container {
                width: 30%;
                text-align: center;
            }

            .logo {
                max-width: 100px;
                height: auto;
            }

            .apotek-info {
                width: 70%;
                padding-left: 20px;
                text-align: left;
            }

            .apotek-name {
                font-size: 18pt;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .divider {
                border-top: 2px solid #000;
                margin: 10px 0;
            }

            .title {
                font-size: 16pt;
                font-weight: bold;
                text-align: center;
                margin: 20px 0;
            }

            .info-section {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
            }

            .info-left,
            .info-right {
                width: 48%;
            }

            .info-row {
                margin-bottom: 5px;
                display: flex;
            }

            .info-label {
                font-weight: bold;
                width: 40%;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            th {
                background-color: #f2f2f2;
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            td {
                border: 1px solid #ddd;
                padding: 8px;
            }

            .item-no {
                width: 5%;
                text-align: center;
            }

            .item-name {
                width: 35%;
            }

            .item-batch {
                width: 15%;
            }

            .item-qty {
                width: 10%;
                text-align: center;
            }

            .item-price {
                width: 15%;
                text-align: right;
            }

            .item-disc {
                width: 10%;
                text-align: right;
            }

            .item-total {
                width: 15%;
                text-align: right;
            }

            .summary {
                width: 50%;
                margin-left: auto;
            }

            .summary-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }

            .summary-label {
                font-weight: bold;
            }

            .grand-total {
                font-size: 14pt;
                font-weight: bold;
            }

            .signature-section {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
            }

            .signature-box {
                width: 30%;
                text-align: center;
            }

            .signature-line {
                border-top: 1px solid #000;
                margin-top: 70px;
                margin-bottom: 5px;
            }

            .footer {
                margin-top: 50px;
                text-align: center;
                font-size: 10pt;
                color: #666;
            }

            /* Responsive adjustments for print */
            @media print {
                body {
                    padding: 0;
                    margin: 0;
                }

                .faktur {
                    max-width: 100%;
                }
            }
        </style>
    </head>

    <body>
        <div class="faktur">
            <div class="header">
                <div class="logo-container">
                    @if ($setting->logo)
                        <img src="{{ asset($setting->logo) }}" alt="Logo" class="logo">
                    @endif
                </div>
                <div class="apotek-info">
                    <div class="apotek-name">{{ $setting->nama_apotek }}</div>
                    <div>{{ $setting->alamat }}</div>
                    <div>Telp: {{ $setting->telepon }}</div>
                    @if ($setting->email)
                        <div>Email: {{ $setting->email }}</div>
                    @endif
                </div>
            </div>

            <div class="divider"></div>

            <div class="title">FAKTUR PENJUALAN</div>

            <div class="info-section">
                <div class="info-left">
                    <div class="info-row">
                        <div class="info-label">No. Faktur</div>
                        <div>: {{ $penjualan->no_faktur }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tanggal</div>
                        <div>: {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="info-right">
                    <div class="info-row">
                        <div class="info-label">Pasien</div>
                        <div>: {{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Petugas</div>
                        <div>: {{ $penjualan->user->name }}</div>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="item-no">No</th>
                        <th class="item-name">Nama Item</th>
                        <th class="item-batch">No. Batch</th>
                        <th class="item-qty">Qty</th>
                        <th class="item-price">Harga</th>
                        <th class="item-disc">Diskon</th>
                        <th class="item-total">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan->details as $index => $detail)
                        <tr>
                            <td class="item-no">{{ $index + 1 }}</td>
                            <td class="item-name">{{ $detail->obat->nama_obat }} ({{ $detail->satuan->nama }})</td>
                            <td class="item-batch">{{ $detail->no_batch }}</td>
                            <td class="item-qty">{{ $detail->jumlah }}</td>
                            <td class="item-price">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                            <td class="item-disc">Rp {{ number_format($detail->diskon, 0, ',', '.') }}</td>
                            <td class="item-total">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-row">
                    <div class="summary-label">Subtotal</div>
                    <div>Rp {{ $penjualan->formatted_subtotal }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">Diskon</div>
                    <div>Rp {{ $penjualan->formatted_diskon_total }}</div>
                </div>
                <div class="summary-row">
                    <div class="summary-label">PPN</div>
                    <div>Rp {{ $penjualan->formatted_ppn_total }}</div>
                </div>
                <div class="summary-row grand-total">
                    <div class="summary-label">Grand Total</div>
                    <div>Rp {{ $penjualan->formatted_grand_total }}</div>
                </div>

                @if ($penjualan->jenis === 'TUNAI')
                    <div class="summary-row">
                        <div class="summary-label">Tunai</div>
                        <div>Rp {{ $penjualan->formatted_bayar }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Kembalian</div>
                        <div>Rp {{ $penjualan->formatted_kembalian }}</div>
                    </div>
                @else
                    <div class="summary-row">
                        <div class="summary-label">Metode Pembayaran</div>
                        <div>Non Tunai</div>
                    </div>
                @endif
            </div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Hormat Kami</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Penerima</div>
                </div>
            </div>

            <div class="footer">
                <p>Terima kasih atas kepercayaan Anda berbelanja di {{ $setting->nama_apotek }}</p>
                <p>Semoga lekas sembuh</p>
            </div>
        </div>
    </body>

</html>
