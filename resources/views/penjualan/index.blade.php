@extends('layout.app')

@section('title', 'Daftar Penjualan')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Daftar Penjualan</h3>
            <div class="card-toolbar">
                <a href="{{ route('penjualan.create') }}" class="btn btn-sm btn-primary">
                    <i class="ki-outline ki-plus fs-2"></i>Tambah Penjualan
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-100px">No</th>
                            <th class="min-w-150px">No Faktur</th>
                            <th class="min-w-150px">Tanggal</th>
                            <th class="min-w-150px">Pasien</th>
                            <th class="min-w-150px">Jenis</th>
                            <th class="min-w-150px">Total</th>
                            <th class="min-w-150px">User</th>
                            <th class="min-w-100px text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualans as $index => $penjualan)
                            <tr>
                                <td>{{ $penjualans->firstItem() + $index }}</td>
                                <td>{{ $penjualan->no_faktur }}</td>
                                <td>{{ $penjualan->tanggal_penjualan->format('d/m/Y') }}</td>
                                <td>{{ $penjualan->pasien ? $penjualan->pasien->nama : 'Umum' }}</td>
                                <td>{{ $penjualan->jenis_display }}</td>
                                <td>Rp {{ $penjualan->formatted_grand_total }}</td>
                                <td>{{ $penjualan->user->name }}</td>
                                <td class="text-end">
                                    <a href="{{ route('penjualan.show', $penjualan->id) }}"
                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary">
                                        <i class="ki-outline ki-eye fs-2"></i>
                                    </a>
                                    <a href="{{ route('penjualan.print', $penjualan->id) }}"
                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-success" target="_blank">
                                        <i class="ki-outline ki-printer fs-2"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data penjualan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-5">
                {{ $penjualans->links() }}
            </div>
        </div>
    </div>
@endsection
