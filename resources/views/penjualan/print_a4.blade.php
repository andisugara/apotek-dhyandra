<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Faktur Penjualan - {{ $penjualan->no_faktur }}</title>
        <style>
            @page {
                margin: 10mm;
            }

            body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                margin: 0;
                padding: 0;
                font-size: 11pt;
                color: #333;
                line-height: 1.4;
            }

            .faktur {
                width: 100%;
                margin: 0 auto;
                background-color: #fff;
                position: relative;
                max-width: none;
                /* Gunakan seluruh lebar halaman */
                padding: 0;
            }

            /* Header Styling - Completely Redesigned */
            .header {
                margin-bottom: 15px;
                width: 100%;
            }

            /* Top Section with Logo and Apotek Info */
            .header-top {
                display: flex;
                align-items: center;
                border-bottom: 2px solid #2c3e50;
                padding-bottom: 10px;
                margin-bottom: 5px;
            }

            .logo-container {
                width: 15%;
                padding-right: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo {
                max-width: 100px;
                height: auto;
            }

            .apotek-info {
                width: 85%;
                padding-left: 10px;
            }

            .apotek-name {
                font-size: 18pt;
                font-weight: bold;
                color: #2c3e50;
                margin-bottom: 2px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* Faktur Title Banner */
            .faktur-banner {
                background-color: #2c3e50;
                color: white;
                text-align: center;
                padding: 8px 0;
                margin-bottom: 5px;
                border-radius: 3px;
                position: relative;
                width: 100%;
            }

            .faktur-title {
                font-size: 14pt;
                font-weight: bold;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin: 0;
            }

            /* Faktur Detail Section - Horizontal Layout */
            .faktur-detail {
                background-color: #f8f9fa;
                padding: 10px;
                border-radius: 3px;
                margin-top: 5px;
            }

            .faktur-detail table {
                width: 100%;
            }

            .faktur-detail td {
                padding: 4px 0;
                vertical-align: top;
            }

            .faktur-number {
                font-weight: bold;
                color: #e74c3c;
            }

            .faktur-number {
                font-size: 12pt;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .info-section {
                display: flex;
                margin-bottom: 10px;
                padding: 8px;
                background-color: #f8f9fa;
                border-radius: 3px;
                width: 100%;
            }

            .info-left {
                width: 60%;
            }

            .info-right {
                width: 40%;
            }

            .info-row {
                display: flex;
                margin-bottom: 5px;
            }

            .info-label {
                font-weight: bold;
                width: 130px;
            }

            .info-value {
                flex: 1;
            }

            /* Table Styling - Enhanced */
            .item-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
                margin-bottom: 10px;
                font-size: 10pt;
                table-layout: fixed;
                /* Memastikan lebar kolom tetap */
                border: 1px solid #ddd;
            }

            .item-table th {
                background-color: #2c3e50;
                color: white;
                padding: 8px 5px;
                text-align: left;
                font-weight: bold;
            }

            .item-table td {
                padding: 7px 5px;
                border-bottom: 1px solid #ddd;
                vertical-align: top;
            }

            .item-table tr:nth-child(even) {
                background-color: #f5f7fa;
            }

            .item-table tr:hover {
                background-color: #f0f2f5;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            /* Notes Section */
            .notes-section {
                margin-top: 15px;
                width: 100%;
            }

            /* Signature Section - Simplified */
            .signature-section {
                margin-top: 30px;
                page-break-inside: avoid;
                width: 100%;
                padding-top: 10px;
                border-top: 1px dashed #ccc;
                text-align: right;
            }

            .signature-box {
                width: 30%;
                text-align: center;
                display: inline-block;
            }

            .signature-line {
                border-top: 1px solid #2c3e50;
                margin-top: 50px;
                margin-bottom: 5px;
                width: 80%;
                display: inline-block;
            }

            /* Footer - Enhanced */
            .footer {
                margin-top: 20px;
                text-align: center;
                font-size: 9pt;
                color: #555;
                border-top: 2px solid #2c3e50;
                padding-top: 10px;
                page-break-inside: avoid;
                width: 100%;
                background-color: #f8f9fa;
                border-radius: 0 0 4px 4px;
                padding-bottom: 10px;
            }

            .terms {
                margin-top: 15px;
                border: 1px solid #ddd;
                border-left: 3px solid #2c3e50;
                padding: 10px;
                font-size: 9pt;
                background-color: #f8f9fa;
                border-radius: 0 4px 4px 0;
            }

            .terms-title {
                font-weight: bold;
                margin-bottom: 5px;
                color: #2c3e50;
                text-transform: uppercase;
                font-size: 10pt;
            }

            .expired-date {
                color: #e74c3c;
                font-weight: bold;
                background-color: #ffeaea;
                padding: 2px 4px;
                border-radius: 3px;
                border: 1px dashed #e74c3c;
            }

            .watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 100pt;
                color: rgba(230, 230, 230, 0.5);
                z-index: -1;
                white-space: nowrap;
            }

            .payment-method {
                font-weight: bold;
                color: #2c3e50;
            }

            .barcode-container {
                margin-top: 10px;
                text-align: right;
            }

            @media print {
                body {
                    padding: 0;
                    margin: 0;
                }

                .faktur {
                    max-width: 100%;
                    box-shadow: none;
                    margin: 0;
                    padding: 0;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        <div class="faktur">
            <!-- Watermark for copy or draft -->
            <!-- <div class="watermark">SALINAN</div> -->

            <!-- Completely Redesigned Header Section -->
            <div class="header">
                <!-- Top Section with Logo and Apotek Info -->
                <div class="header-top">
                    <div class="logo-container">
                        @if ($setting->logo)
                            <img src="{{ asset($setting->logo) }}" alt="Logo" class="logo">
                        @endif
                    </div>
                    <div class="apotek-info">
                        <div class="apotek-name">{{ $setting->nama_apotek }}</div>
                        <div style="font-size: 11pt;">{{ $setting->alamat }}</div>
                        <div>Telp: {{ $setting->telepon }}
                            @if ($setting->email)
                                &nbsp;&nbsp;|&nbsp;&nbsp;Email: {{ $setting->email }}
                            @endif
                            @if ($setting->website)
                                &nbsp;&nbsp;|&nbsp;&nbsp;Website: {{ $setting->website }}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Faktur Title Banner -->
                <div class="faktur-banner">
                    <div class="faktur-title">FAKTUR PENJUALAN</div>
                </div>

                <!-- Faktur Detail Section - Horizontal Format -->
                <div class="faktur-detail">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11pt;">
                        <tr>
                            <td style="width: 15%;"><strong>No. Faktur</strong></td>
                            <td style="width: 25%;">: <span class="faktur-number">{{ $penjualan->no_faktur }}</span>
                            </td>
                            <td style="width: 15%;"><strong>Nama Pasien</strong></td>
                            <td style="width: 45%;">: {{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal</strong></td>
                            <td>: {{ $penjualan->tanggal_penjualan->format('d F Y') }}</td>
                            <td><strong>Petugas</strong></td>
                            <td>: {{ $penjualan->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Waktu</strong></td>
                            <td>: {{ $penjualan->created_at->format('H:i:s') }}</td>
                            <td><strong>Pembayaran</strong></td>
                            <td>: <span
                                    class="payment-method">{{ $penjualan->jenis === 'TUNAI' ? 'Tunai' : 'Non Tunai' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Additional Details (Optional) -->
                @if (($penjualan->pasien && ($penjualan->pasien->alamat || $penjualan->pasien->telepon)) || $penjualan->keterangan)
                    <div
                        style="background-color: #f0f2f5; padding: 5px 8px; margin-top: 5px; border-radius: 3px; font-size: 10pt;">
                        @if ($penjualan->pasien && $penjualan->pasien->alamat)
                            <div><strong>Alamat Pasien:</strong> {{ $penjualan->pasien->alamat }}</div>
                        @endif

                        @if ($penjualan->pasien && $penjualan->pasien->telepon)
                            <div><strong>Telepon Pasien:</strong> {{ $penjualan->pasien->telepon }}</div>
                        @endif

                        @if ($penjualan->keterangan)
                            <div><strong>Keterangan:</strong> {{ $penjualan->keterangan }}</div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Item Table with Summary -->
            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 30%;">Nama Obat</th>
                        <th style="width: 10%;">Satuan</th>
                        <th style="width: 10%;" class="text-right">Harga</th>
                        <th style="width: 7%;" class="text-center">Qty</th>
                        <th style="width: 8%;" class="text-right">Diskon</th>
                        <th style="width: 12%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penjualan->details as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $detail->obat->nama_obat }}</strong>
                                @if ($detail->obat->kode_obat)
                                    <div style="font-size: 8pt; color: #666; margin-top: 2px;">Kode:
                                        {{ $detail->obat->kode_obat }}</div>
                                @endif
                                @if ($detail->obat->kandungan)
                                    <div style="font-size: 8pt; color: #666; font-style: italic; margin-top: 1px;">
                                        {{ $detail->obat->kandungan }}</div>
                                @endif
                            </td>
                            <td>{{ $detail->satuan->nama }}</td>
                            <td class="text-right">{{ number_format($detail->harga, 0, ',', '.') }}</td>
                            <td class="text-center" style="font-weight: bold;">{{ $detail->jumlah }}</td>
                            <td class="text-right">
                                @if ($detail->diskon > 0)
                                    <span
                                        style="color: #e74c3c;">{{ number_format($detail->diskon, 0, ',', '.') }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: bold;">
                                {{ number_format($detail->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach

                    <!-- Summary rows integrated into the table -->
                    <tr>
                        <td colspan="5" class="text-right" style="border-top: 2px solid #2c3e50; padding-top: 10px;">
                            <strong>Subtotal</strong>
                        </td>
                        <td colspan="2" class="text-right" style="border-top: 2px solid #2c3e50; padding-top: 10px;">
                            <strong>Rp {{ $penjualan->formatted_subtotal }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"
                            style="{{ $penjualan->diskon_total > 0 ? 'color: #e74c3c;' : '' }}">
                            <strong>Diskon</strong>
                        </td>
                        <td colspan="2" class="text-right"
                            style="{{ $penjualan->diskon_total > 0 ? 'color: #e74c3c;' : '' }}">
                            <strong>Rp {{ $penjualan->formatted_diskon_total }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>PPN</strong>
                        </td>
                        <td colspan="2" class="text-right">
                            <strong>Rp {{ $penjualan->formatted_ppn_total }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>Tuslah</strong>
                        </td>
                        <td colspan="2" class="text-right">
                            <strong>Rp {{ $penjualan->formatted_tuslah_total }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right">
                            <strong>Embalase</strong>
                        </td>
                        <td colspan="2" class="text-right">
                            <strong>Rp {{ $penjualan->formatted_embalase_total }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"
                            style="background-color: #2c3e50; color: white; font-weight: bold; padding: 8px 5px;">
                            <strong>GRAND TOTAL</strong>
                        </td>
                        <td colspan="2" class="text-right"
                            style="background-color: #2c3e50; color: white; font-weight: bold; padding: 8px 5px;">
                            <strong>Rp {{ $penjualan->formatted_grand_total }}</strong>
                        </td>
                    </tr>

                    @if ($penjualan->jenis === 'TUNAI')
                        <tr>
                            <td colspan="5" class="text-right" style="background-color: #f0f4f8;">
                                <strong>Tunai</strong>
                            </td>
                            <td colspan="2" class="text-right" style="background-color: #f0f4f8;">
                                <strong>Rp {{ $penjualan->formatted_bayar }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-right" style="background-color: #f0f4f8; font-weight: bold;">
                                <strong>Kembalian</strong>
                            </td>
                            <td colspan="2" class="text-right" style="background-color: #f0f4f8; font-weight: bold;">
                                <strong>Rp {{ $penjualan->formatted_kembalian }}</strong>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Notes and Terms Section -->
            {{-- <div style="display: flex; margin-top: 20px;">
                <div style="width: 100%;">
                    <div
                        style="padding: 10px; background-color: #f8f9fa; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 15px;">
                        <div
                            style="font-weight: bold; margin-bottom: 5px; color: #2c3e50; text-transform: uppercase; font-size: 10pt;">
                            <i class="fa fa-pencil-alt"></i> Catatan Pembelian:
                        </div>
                        <div
                            style="padding: 5px; background-color: white; border-left: 3px solid #2c3e50; min-height: 30px;">
                            {{ $penjualan->keterangan ?? 'Tidak ada catatan khusus' }}</div>
                    </div>

                    <div class="terms">
                        <div class="terms-title">
                            <i class="fa fa-info-circle"></i> Syarat & Ketentuan
                        </div>
                        <ol style="margin: 5px 0 0 20px; padding: 0; line-height: 1.5;">
                            <li>Barang yang sudah dibeli tidak dapat dikembalikan</li>
                            <li>Obat keras tidak dapat ditukar atau dikembalikan</li>
                            <li>Pembayaran dianggap lunas setelah cek/giro cair</li>
                            <li>Faktur ini adalah bukti resmi pembelian</li>
                            <li>Simpan faktur ini sebagai bukti garansi</li>
                        </ol>
                    </div>

                    <!-- Faktur info -->
                    <div
                        style="margin-top: 15px; text-align: center; font-size: 8pt; color: #777; border-top: 1px dashed #ddd; padding-top: 10px;">
                        No. Faktur: {{ $penjualan->no_faktur }}
                        <br>Tanggal: {{ $penjualan->tanggal_penjualan->format('d F Y') }}
                    </div>
                </div>
            </div> --}}

            <!-- Signature Section - Right Aligned Only -->
            <div class="signature-section">
                <div class="signature-box">
                    {{-- <div class="signature-line"></div> --}}
                    <div>Hormat Kami,</div>
                    {{-- <div style="font-size: 9pt; margin-top: 5px;">{{ $setting->nama_apotek }}</div> --}}
                    <div style="font-size: 9pt; margin-top: 35px;">{{ $setting->nama_apotek }}</div>
                </div>
            </div>

            <!-- Footer - Enhanced -->
            <div class="footer">
                <div style="font-weight: bold; font-size: 10pt; margin-bottom: 5px; color: #2c3e50;">
                    Terima kasih atas kepercayaan Anda berbelanja di {{ $setting->nama_apotek }}
                </div>
                <div style="font-style: italic;">Semoga lekas sembuh dan sehat selalu.</div>
                <div
                    style="margin-top: 8px; font-size: 8pt; color: #777; display: flex; justify-content: space-between; padding: 0 20px;">
                    <span>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</span>
                    <span>Petugas: {{ $penjualan->user->name }}</span>
                </div>
            </div>
        </div>
    </body>

</html>
