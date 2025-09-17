@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Detail Retur Penjualan</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('retur_penjualan.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-7">
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="ps-0 w-150px">No. Retur</th>
                            <td>: {{ $returPenjualan->no_retur }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Tanggal Retur</th>
                            <td>: {{ $returPenjualan->tanggal_retur->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">No. Faktur</th>
                            <td>: {{ $returPenjualan->penjualan->no_faktur }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Pasien</th>
                            <td>: {{ $returPenjualan->penjualan->pasien ? $returPenjualan->penjualan->pasien->nama : '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="ps-0 w-150px">Alasan</th>
                            <td>: {{ $returPenjualan->alasan }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Petugas</th>
                            <td>: {{ $returPenjualan->user ? $returPenjualan->user->name : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="ps-0">Grand Total</th>
                            <td>: Rp {{ $returPenjualan->formatted_grand_total }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="separator separator-dashed my-8"></div>

            <div class="row mb-7">
                <div class="col">
                    <h4>Detail Item</h4>
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-dashed gy-4">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th>No</th>
                                    <th>Obat</th>
                                    <th>Satuan</th>
                                    <th>No. Batch</th>
                                    <th>Exp. Date</th>
                                    <th>Lokasi</th>
                                    <th>Jumlah</th>
                                    <th>Harga Jual</th>
                                    <th>Subtotal</th>
                                    <th>Diskon</th>
                                    <th>PPN</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returPenjualan->details as $index => $detail)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $detail->obat ? $detail->obat->nama_obat : 'Obat tidak ditemukan' }}</td>
                                        <td>{{ $detail->satuan ? $detail->satuan->nama : 'Satuan tidak ditemukan' }}</td>
                                        <td>{{ $detail->no_batch }}</td>
                                        <td>{{ $detail->tanggal_expired ? $detail->tanggal_expired->format('d/m/Y') : '-' }}
                                        </td>
                                        <td>{{ $detail->lokasi ? $detail->lokasi->nama : 'Lokasi tidak ditemukan' }}</td>
                                        <td>{{ $detail->jumlah }}</td>
                                        <td>Rp {{ $detail->formatted_harga_jual }}</td>
                                        <td>Rp {{ $detail->formatted_subtotal }}</td>
                                        <td>Rp {{ $detail->formatted_diskon }}</td>
                                        <td>Rp {{ $detail->formatted_ppn }}</td>
                                        <td>Rp {{ $detail->formatted_total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold fs-6">
                                    <td colspan="8" class="text-end">Total</td>
                                    <td>Rp {{ $returPenjualan->formatted_subtotal }}</td>
                                    <td>Rp {{ $returPenjualan->formatted_diskon_total }}</td>
                                    <td>Rp {{ $returPenjualan->formatted_ppn_total }}</td>
                                    <td>Rp {{ $returPenjualan->formatted_grand_total }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if ($returPenjualan->transaksiAkun && $returPenjualan->transaksiAkun->count() > 0)
                <div class="separator separator-dashed my-8"></div>

                <div class="row">
                    <div class="col">
                        <h4>Transaksi Akuntansi</h4>
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-dashed gy-4">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800">
                                        <th>No</th>
                                        <th>Akun</th>
                                        <th>Tanggal</th>
                                        <th>Deskripsi</th>
                                        <th>Debit</th>
                                        <th>Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($returPenjualan->transaksiAkun as $index => $transaksi)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $transaksi->akun ? $transaksi->akun->nama : 'Akun tidak ditemukan' }}
                                            </td>
                                            <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $transaksi->deskripsi }}</td>
                                            <td>Rp {{ number_format($transaksi->debit, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format($transaksi->kredit, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
