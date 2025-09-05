@extends('layout.app')
@section('title', 'Detail Pengeluaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Pengeluaran</h3>
            <div class="card-toolbar">
                <a href="{{ route('pengeluaran.index') }}" class="btn btn-sm btn-secondary">
                    Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-6">
                <div class="col-lg-4">
                    <div class="fw-semibold">Nama Pengeluaran</div>
                    <div>{{ $pengeluaran->nama }}</div>
                </div>

                <div class="col-lg-4">
                    <div class="fw-semibold">Tanggal</div>
                    <div>{{ $pengeluaran->tanggal->format('d/m/Y') }}</div>
                </div>

                <div class="col-lg-4">
                    <div class="fw-semibold">Jumlah</div>
                    <div>Rp {{ $pengeluaran->formatted_jumlah }}</div>
                </div>
            </div>

            <div class="row mb-6">
                <div class="col-lg-4">
                    <div class="fw-semibold">Dibuat Oleh</div>
                    <div>{{ $pengeluaran->user->name ?? 'Unknown' }}</div>
                </div>

                <div class="col-lg-4">
                    <div class="fw-semibold">Tanggal Dibuat</div>
                    <div>{{ $pengeluaran->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="col-lg-4">
                    <div class="fw-semibold">Terakhir Diperbarui</div>
                    <div>{{ $pengeluaran->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <div class="separator my-8"></div>

            <!-- Transaksi Akuntansi -->
            <h4 class="mb-4">Transaksi Akuntansi</h4>

            @if ($pengeluaran->transaksiAkun->count() > 0)
                <table class="table table-striped table-row-bordered">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th>No</th>
                            <th>Akun</th>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Debit (Rp)</th>
                            <th>Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengeluaran->transaksiAkun as $index => $transaksi)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $transaksi->akun->kode }} - {{ $transaksi->akun->nama }}</td>
                                <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $transaksi->deskripsi }}</td>
                                <td class="text-end">{{ $transaksi->debit > 0 ? $transaksi->formatted_debit : '-' }}</td>
                                <td class="text-end">{{ $transaksi->kredit > 0 ? $transaksi->formatted_kredit : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">
                    Tidak ada transaksi akuntansi yang terkait dengan pengeluaran ini.
                </div>
            @endif
        </div>
    </div>
@endsection
