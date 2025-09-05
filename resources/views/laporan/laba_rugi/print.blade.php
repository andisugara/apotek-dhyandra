<!DOCTYPE html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Laporan Laba Rugi</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 20px;
            }

            h1 {
                text-align: center;
                font-size: 18px;
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
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .address {
                margin-bottom: 20px;
                font-size: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            .report-table {
                width: 100%;
                margin-bottom: 30px;
            }

            .report-table td {
                padding: 8px;
                border-bottom: 1px solid #f2f2f2;
            }

            .section-header {
                font-weight: bold;
                font-size: 14px;
                padding: 10px 5px;
                background-color: #f9f9f9;
            }

            .subsection {
                padding-left: 25px;
            }

            .total-row {
                font-weight: bold;
            }

            .text-end {
                text-align: right;
            }

            .profit-positive {
                color: #28a745;
            }

            .profit-negative {
                color: #dc3545;
            }

            .expense-table {
                width: 100%;
                border-collapse: collapse;
            }

            .expense-table th,
            .expense-table td {
                border: 1px solid #000;
                padding: 8px;
            }

            .expense-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .bg-light {
                background-color: #f9f9f9;
            }

            @media print {
                body {
                    margin: 0;
                    padding: 10mm;
                }

                .no-print {
                    display: none;
                }
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

        <h1>LAPORAN LABA RUGI</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</h2>

        <table class="report-table">
            <!-- Pendapatan -->
            <tr class="section-header">
                <td colspan="2">Pendapatan</td>
            </tr>
            <tr>
                <td class="subsection">Penjualan</td>
                <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Pendapatan</td>
                <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>

            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>

            <!-- Harga Pokok Penjualan -->
            <tr class="section-header">
                <td colspan="2">Harga Pokok Penjualan (HPP)</td>
            </tr>
            <tr>
                <td class="subsection">Harga Pokok Penjualan Obat</td>
                <td class="text-end">Rp {{ number_format($costOfGoodsSold, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total HPP</td>
                <td class="text-end">Rp {{ number_format($costOfGoodsSold, 0, ',', '.') }}</td>
            </tr>

            <tr class="total-row bg-light">
                <td>Laba Kotor</td>
                <td class="text-end">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
            </tr>

            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>

            <!-- Pengeluaran -->
            <tr class="section-header">
                <td colspan="2">Pengeluaran</td>
            </tr>
            @if ($expenses->count() > 0)
                @foreach ($expenses as $expense)
                    <tr>
                        <td class="subsection">{{ $expense->nama }}</td>
                        <td class="text-end">Rp {{ number_format($expense->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="subsection" colspan="2">Tidak ada pengeluaran dalam periode ini</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total Pengeluaran</td>
                <td class="text-end">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
            </tr>

            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>

            <!-- Laba Bersih -->
            <tr class="total-row bg-light">
                <td>Laba Bersih</td>
                <td class="text-end {{ $netProfit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </td>
            </tr>
        </table>

        <h3>Rasio Keuangan</h3>
        <table class="expense-table">
            <tr>
                <th>Margin Laba Kotor</th>
                <th>Margin Laba Bersih</th>
                <th>Rasio Biaya</th>
            </tr>
            <tr>
                <td class="text-end">{{ number_format($grossProfitMargin, 2) }}%</td>
                <td class="text-end {{ $netProfitMargin >= 0 ? 'profit-positive' : 'profit-negative' }}">
                    {{ number_format($netProfitMargin, 2) }}%</td>
                <td class="text-end">{{ number_format($expenseRatio, 2) }}%</td>
            </tr>
        </table>

        <h3>Detail Pengeluaran</h3>
        <table class="expense-table">
            <tr>
                <th>Nama Pengeluaran</th>
                <th>Tanggal</th>
                <th class="text-end">Jumlah</th>
                <th>Keterangan</th>
            </tr>
            @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->nama }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-end">Rp {{ number_format($expense->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $expense->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center">Tidak ada data pengeluaran</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-end">Total</td>
                <td class="text-end">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </table>

        <div style="text-align: right; margin-top: 50px;">
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
            <p style="margin-top: 50px;">_________________________</p>
            <p>Apoteker Penanggung Jawab</p>
        </div>

        <div class="no-print" style="margin-top: 30px; text-align: center;">
            <button onclick="window.print()"
                style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Cetak
                Laporan</button>
        </div>
    </body>

</html>
