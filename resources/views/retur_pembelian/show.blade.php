@extends('layout.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>Detail Retur Pembelian</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('retur_pembelian.index') }}" class="btn btn-sm btn-secondary">
                <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
            </a>
        </div>
    </div>
    <div class="card-body py-4">
        <!-- Retur Details -->
        <div class="row mb-6">
            <div class="col-lg-6">
                <table class="table table-borderless">
                    <tr>
                        <th class="fw-bold w-150px">No Retur</th>
                        <td>{{ $returPembelian->no_retur }}</td>
                    </tr>
                    <tr>
                        <th class="fw-bold">Tanggal Retur</th>
                        <td>{{ $returPembelian->tanggal_retur->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th class="fw-bold">User</th>
                        <td>{{ $returPembelian->user ? $returPembelian->user->name : '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-lg-6">
                <table class="table table-borderless">
                    <tr>
                        <th class="fw-bold w-150px">No Faktur</th>
                        <td>{{ $returPembelian->pembelian->no_faktur }}</td>
                    </tr>
                    <tr>
                        <th class="fw-bold">Supplier</th>
                        <td>{{ $returPembelian->pembelian->supplier->nama }}</td>
                    </tr>
                    <tr>
                        <th class="fw-bold">Alasan Retur</th>
                        <td>{{ $returPembelian->alasan }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="separator separator-dashed my-5"></div>
        
        <!-- Detail Items -->
        <h4 class="mb-5">Detail Item Retur</h4>
        
        <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
            <table class="table table-row-bordered gs-7">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800">
                        <th>No</th>
                        <th>Obat</th>
                        <th>Satuan</th>
                        <th>No Batch</th>
                        <th>Expired</th>
                        <th>Lokasi</th>
                        <th>Jumlah</th>
                        <th>Harga Beli</th>
                        <th>Subtotal</th>
                        <th>PPN</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returPembelian->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->obat->nama_obat }}</td>
                        <td>{{ $detail->satuan->nama }}</td>
                        <td>{{ $detail->no_batch }}</td>
                        <td>{{ $detail->tanggal_expired->format('d/m/Y') }}</td>
                        <td>{{ $detail->lokasi->nama }}</td>
                        <td>{{ $detail->jumlah }}</td>
                        <td>Rp {{ $detail->formatted_harga_beli }}</td>
                        <td>Rp {{ $detail->formatted_subtotal }}</td>
                        <td>Rp {{ $detail->formatted_ppn }}</td>
                        <td>Rp {{ $detail->formatted_total }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="8" class="text-end">Subtotal:</th>
                        <td colspan="3" class="fw-bold">Rp {{ $returPembelian->formatted_subtotal }}</td>
                    </tr>
                    <tr>
                        <th colspan="8" class="text-end">PPN:</th>
                        <td colspan="3" class="fw-bold">Rp {{ $returPembelian->formatted_ppn_total }}</td>
                    </tr>
                    <tr>
                        <th colspan="8" class="text-end">Grand Total:</th>
                        <td colspan="3" class="fw-bold">Rp {{ $returPembelian->formatted_grand_total }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        @if($returPembelian->transaksiAkun->count() > 0)
        <div class="separator separator-dashed my-5"></div>
        
        <!-- Transaksi Keuangan -->
        <h4 class="mb-5">Transaksi Keuangan</h4>
        
        <div class="table-responsive">
            <table class="table table-row-bordered gs-7">
                <thead>
                    <tr class="fw-bold fs-6 text-gray-800">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Akun</th>
                        <th>Kode Referensi</th>
                        <th>Deskripsi</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returPembelian->transaksiAkun as $index => $transaksi)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $transaksi->akun->nama }}</td>
                        <td>{{ $transaksi->kode_referensi }}</td>
                        <td>{{ $transaksi->deskripsi }}</td>
                        <td>{{ $transaksi->debit > 0 ? 'Rp ' . number_format($transaksi->debit, 0, ',', '.') : '-' }}</td>
                        <td>{{ $transaksi->kredit > 0 ? 'Rp ' . number_format($transaksi->kredit, 0, ',', '.') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
    </div>
</div>
@endsection
