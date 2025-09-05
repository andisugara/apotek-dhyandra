@extends('layout.app')

@section('title', 'Detail Penjualan')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Detail Penjualan</h3>
            <div class="card-toolbar">
                <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-light-primary me-2">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Kembali
                </a>
                <a href="{{ route('penjualan.print', $penjualan->id) }}" class="btn btn-sm btn-primary" target="_blank">
                    <i class="ki-outline ki-printer fs-2"></i>Cetak Struk
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-5">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25">No Faktur</th>
                            <td>: {{ $penjualan->no_faktur }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>: {{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Pasien</th>
                            <td>: {{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td>: {{ $penjualan->jenis_display }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>: {{ $penjualan->keterangan ?: '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25">Subtotal</th>
                            <td>: Rp {{ $penjualan->formatted_subtotal }}</td>
                        </tr>
                        <tr>
                            <th>Diskon</th>
                            <td>: Rp {{ $penjualan->formatted_diskon_total }}</td>
                        </tr>
                        <tr>
                            <th>PPN</th>
                            <td>: Rp {{ $penjualan->formatted_ppn_total }}</td>
                        </tr>
                        <tr>
                            <th>Grand Total</th>
                            <td class="fw-bold">: Rp {{ $penjualan->formatted_grand_total }}</td>
                        </tr>
                        <tr>
                            <th>Bayar</th>
                            <td>: Rp {{ $penjualan->formatted_bayar }}</td>
                        </tr>
                        <tr>
                            <th>Kembalian</th>
                            <td>: Rp {{ $penjualan->formatted_kembalian }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <h4>Item Detail</h4>
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th>No</th>
                                    <th>Nama Obat</th>
                                    <th>Batch</th>
                                    <th>Expired</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Diskon</th>
                                    <th>PPN</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penjualan->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold">{{ $detail->obat->nama_obat }}</div>
                                                <div class="text-muted">{{ $detail->obat->kode_obat }}</div>
                                            </div>
                                        </td>
                                        <td>{{ $detail->no_batch }}</td>
                                        <td>{{ $detail->tanggal_expired ? $detail->tanggal_expired->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>{{ $detail->satuan->nama_satuan }}</td>
                                        <td>Rp {{ $detail->formatted_harga }}</td>
                                        <td>{{ $detail->jumlah }}</td>
                                        <td>Rp {{ $detail->formatted_diskon }}</td>
                                        <td>Rp {{ $detail->formatted_ppn }}</td>
                                        <td>Rp {{ $detail->formatted_total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($penjualan->transaksiAkun->count() > 0)
                <div class="row mt-5">
                    <div class="col-12">
                        <h4>Transaksi Akuntansi</h4>
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kode</th>
                                        <th>Akun</th>
                                        <th>Deskripsi</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penjualan->transaksiAkun as $index => $transaksi)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $transaksi->kode_referensi }}</td>
                                            <td>{{ $transaksi->akun->nama_akun }}</td>
                                            <td>{{ $transaksi->deskripsi }}</td>
                                            <td class="text-end">{{ number_format($transaksi->debit, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format($transaksi->kredit, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-5 text-end text-muted">
                <div>Dibuat oleh: {{ $penjualan->user->name }}</div>
                <div>Waktu: {{ $penjualan->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    </div>
@endsection
