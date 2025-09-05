<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Struk Penjualan - {{ $penjualan->no_faktur }}</title>
        <style>
            @page {
                margin: 5mm;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 9pt;
                line-height: 1.3;
                margin: 0;
                padding: 0;
                width: 100%;
            }

            .receipt {
                width: 58mm;
            }

            .header {
                text-align: center;
                margin-bottom: 10px;
            }

            .logo {
                max-width: 100px;
                margin: 0 auto;
                display: block;
            }

            .title {
                font-weight: bold;
                font-size: 12pt;
                margin: 5px 0;
            }

            .address {
                font-size: 8pt;
                margin-bottom: 5px;
            }

            .divider {
                border-top: 1px dashed #000;
                margin: 5px 0;
            }

            .info {
                margin-bottom: 10px;
            }

            .info-row {
                display: flex;
                justify-content: space-between;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 5px 0;
            }

            th {
                text-align: left;
                font-size: 8pt;
            }

            td {
                font-size: 8pt;
            }

            .right {
                text-align: right;
            }

            .item-row {
                padding: 2px 0;
            }

            .total-section {
                margin-top: 5px;
                border-top: 1px dashed #000;
                padding-top: 5px;
            }

            .grand-total {
                font-weight: bold;
                font-size: 10pt;
            }

            .footer {
                text-align: center;
                margin-top: 10px;
                font-size: 8pt;
            }
        </style>
    </head>

    <body>
        <div class="receipt">
            <div class="header">
                @if ($setting->logo)
                    <img src="{{ asset($setting->logo) }}" alt="Logo" class="logo">
                @endif
                <div class="title">{{ $setting->nama_apotek }}</div>
                <div class="address">{{ $setting->alamat }}</div>
                <div class="address">Telp: {{ $setting->telepon }}</div>
            </div>

            <div class="divider"></div>

            <div class="info">
                <div class="info-row">
                    <span>No. Faktur</span>
                    <span>: {{ $penjualan->no_faktur }}</span>
                </div>
                <div class="info-row">
                    <span>Tanggal</span>
                    <span>: {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span>Kasir</span>
                    <span>: {{ $penjualan->user->name }}</span>
                </div>
                <div class="info-row">
                    <span>Pasien</span>
                    <span>: {{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="right">Jml</th>
                        <th class="right">Harga</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan->details as $detail)
                        <tr class="item-row">
                            <td colspan="4">{{ $detail->obat->nama_obat }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="right">{{ $detail->jumlah }}</td>
                            <td class="right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                            <td class="right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-section">
                <div class="info-row">
                    <span>Subtotal</span>
                    <span>Rp {{ $penjualan->formatted_subtotal }}</span>
                </div>
                <div class="info-row">
                    <span>Diskon</span>
                    <span>Rp {{ $penjualan->formatted_diskon_total }}</span>
                </div>
                <div class="info-row">
                    <span>PPN</span>
                    <span>Rp {{ $penjualan->formatted_ppn_total }}</span>
                </div>
                <div class="info-row grand-total">
                    <span>Grand Total</span>
                    <span>Rp {{ $penjualan->formatted_grand_total }}</span>
                </div>

                @if ($penjualan->jenis === 'TUNAI')
                    <div class="info-row">
                        <span>Bayar</span>
                        <span>Rp {{ $penjualan->formatted_bayar }}</span>
                    </div>
                    <div class="info-row">
                        <span>Kembalian</span>
                        <span>Rp {{ $penjualan->formatted_kembalian }}</span>
                    </div>
                @else
                    <div class="info-row">
                        <span>Pembayaran</span>
                        <span>Non Tunai</span>
                    </div>
                @endif
            </div>

            <div class="footer">
                <div class="divider"></div>
                <p>Terima kasih atas kunjungan Anda</p>
                <p>Semoga lekas sembuh</p>
            </div>
        </div>
    </body>

</html>
