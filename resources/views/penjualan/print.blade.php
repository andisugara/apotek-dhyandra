<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Struk Penjualan - {{ $penjualan->no_faktur }}</title>
        <style>
            @page {
                margin: 0;
                padding: 0;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 8pt;
                line-height: 1.2;
                margin: 0;
                padding: 0;
                width: 100%;
                text-align: center;
            }

            .receipt {
                width: 100%;
                max-width: 58mm;
                margin: 0 auto;
                padding: 2mm;
                box-sizing: border-box;
            }

            .header {
                text-align: center;
                margin-bottom: 5px;
                width: 100%;
            }

            .logo {
                max-width: 70px;
                height: auto;
                margin: 0 auto;
                display: block;
            }

            .title {
                font-weight: bold;
                font-size: 9pt;
                margin: 3px 0;
                text-align: center;
                width: 100%;
            }

            .address {
                font-size: 7pt;
                margin-bottom: 2px;
                text-align: center;
                width: 100%;
            }

            .divider {
                border-top: 1px dashed #000;
                margin: 3px 0;
                width: 100%;
            }

            .info {
                margin-bottom: 5px;
                width: 100%;
                text-align: left;
            }

            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 1px;
                width: 100%;
                font-size: 7pt;
                text-align: left;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 3px 0;
                table-layout: fixed;
            }

            th {
                text-align: left;
                font-size: 7pt;
                padding: 1px 0;
            }

            td {
                font-size: 7pt;
                padding: 1px 0;
                word-wrap: break-word;
            }

            /* Column widths for items table */
            table thead tr th:nth-child(1) {
                width: 45%;
                text-align: left;
            }

            /* Item name */
            table thead tr th:nth-child(2) {
                width: 15%;
                text-align: right;
            }

            /* Quantity */
            table thead tr th:nth-child(3) {
                width: 20%;
                text-align: right;
            }

            /* Price */
            table thead tr th:nth-child(4) {
                width: 20%;
                text-align: right;
            }

            /* Total */

            .right {
                text-align: right;
            }

            .item-row {
                padding: 1px 0;
                font-size: 7pt;
            }

            /* Fix for product name column */
            tr.item-row td {
                font-size: 7pt;
                line-height: 1.1;
            }

            .total-section {
                margin-top: 5px;
                border-top: 1px dashed #000;
                padding-top: 5px;
                width: 100%;
                text-align: left;
            }

            .grand-total {
                font-weight: bold;
                font-size: 9pt;
            }

            .total-row {
                font-size: 7pt;
                line-height: 1.5;
                text-align: left;
            }

            .footer {
                text-align: center;
                margin-top: 5px;
                font-size: 7pt;
                width: 100%;
            }

            .footer p {
                margin: 2px 0;
                text-align: center;
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
                <table style="border: none; width: 100%;">
                    <tr>
                        <td style="width: 30%; text-align: left; font-size: 7pt;">No. Faktur</td>
                        <td style="width: 70%; text-align: left; font-size: 7pt;">: {{ $penjualan->no_faktur }}</td>
                    </tr>
                    <tr>
                        <td style="width: 30%; text-align: left; font-size: 7pt;">Tanggal</td>
                        <td style="width: 70%; text-align: left; font-size: 7pt;">:
                            {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="width: 30%; text-align: left; font-size: 7pt;">Kasir</td>
                        <td style="width: 70%; text-align: left; font-size: 7pt;">: {{ $penjualan->user->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 30%; text-align: left; font-size: 7pt;">Pasien</td>
                        <td style="width: 70%; text-align: left; font-size: 7pt;">:
                            {{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">Item</th>
                        <th class="right">Jml</th>
                        <th class="right">Harga</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan->details as $detail)
                        <tr class="item-row">
                            <td colspan="4" style="text-align: left;">{{ $detail->obat->nama_obat }}
                                ({{ $detail->satuan->nama }})
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="text-align: left;"></td>
                            <td width="15%" class="right">{{ $detail->jumlah }}</td>
                            <td width="15%" class="right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                            <td width="20%" class="right">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="total-section">
                <table style="border: none; width: 100%;">
                    <tr class="total-row">
                        <td style="text-align: left;">Subtotal</td>
                        <td style="text-align: right;">Rp {{ $penjualan->formatted_subtotal }}</td>
                    </tr>
                    <tr class="total-row">
                        <td style="text-align: left;">Diskon</td>
                        <td style="text-align: right;">Rp {{ $penjualan->formatted_diskon_total }}</td>
                    </tr>
                    <tr class="total-row">
                        <td style="text-align: left;">PPN</td>
                        <td style="text-align: right;">Rp {{ $penjualan->formatted_ppn_total }}</td>
                    </tr>
                    <tr class="total-row grand-total">
                        <td style="text-align: left;">Grand Total</td>
                        <td style="text-align: right;">Rp {{ $penjualan->formatted_grand_total }}</td>
                    </tr>

                    @if ($penjualan->jenis === 'TUNAI')
                        <tr class="total-row">
                            <td style="text-align: left;">Bayar</td>
                            <td style="text-align: right;">Rp {{ $penjualan->formatted_bayar }}</td>
                        </tr>
                        <tr class="total-row">
                            <td style="text-align: left;">Kembalian</td>
                            <td style="text-align: right;">Rp {{ $penjualan->formatted_kembalian }}</td>
                        </tr>
                    @else
                        <tr class="total-row">
                            <td style="text-align: left;">Pembayaran</td>
                            <td style="text-align: right;">Non Tunai</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="footer">
                <div class="divider"></div>
                <p>Terima kasih atas kunjungan Anda</p>
                <p>Semoga lekas sembuh</p>
            </div>
        </div>
    </body>

</html>
