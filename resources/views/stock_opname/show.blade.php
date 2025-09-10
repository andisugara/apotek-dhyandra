@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Detail Stock Opname</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">{{ $stockOpname->kode }}</span>
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.index') }}" class="btn btn-secondary me-2">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <path d="M9.60001 11H21C21.6 11 22 11.4 22 12C22 12.6 21.6 13 21 13H9.60001V11Z"
                                fill="currentColor" />
                            <path opacity="0.3" d="M9.6 20V4L2.3 11.3C1.9 11.7 1.9 12.3 2.3 12.7L9.6 20Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    Kembali
                </a>
                <a href="{{ route('stock_opname.print', $stockOpname) }}" class="btn btn-primary" target="_blank">
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3"
                                d="M18.9 9.5C18.9 8.1 17.8 7 16.4 7H5.6C4.2 7 3.1 8.1 3.1 9.5C3.1 10.9 4.2 12 5.6 12H16.4C17.8 12 18.9 10.9 18.9 9.5ZM16.4 10.5C16.4 11.3 15.7 12 14.9 12C14.1 12 13.4 11.3 13.4 10.5C13.4 9.7 14.1 9 14.9 9C15.7 9 16.4 9.7 16.4 10.5Z"
                                fill="currentColor"></path>
                            <path d="M7.4 21H14.6V13.9H7.4V21ZM7.4 6V1H14.6V6H7.4Z" fill="currentColor"></path>
                        </svg>
                    </span>
                    Print
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            <!-- Info Section -->
            <div class="mb-10">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 fw-bold" width="200">Kode</th>
                                <td>: {{ $stockOpname->kode }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 fw-bold">Tanggal</th>
                                <td>: {{ date('d/m/Y', strtotime($stockOpname->tanggal)) }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 fw-bold">Status</th>
                                <td>:
                                    @if ($stockOpname->status == 'draft')
                                        <span class="badge badge-light-warning">Draft</span>
                                    @else
                                        <span class="badge badge-light-success">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0 fw-bold" width="200">Petugas</th>
                                <td>: {{ $stockOpname->user->name }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0 fw-bold">Keterangan</th>
                                <td>: {{ $stockOpname->keterangan ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row g-5 g-xl-8 mb-8">
                <div class="col-xl-4">
                    <div class="card bg-light-success card-xl-stretch mb-5">
                        <div class="card-body my-3">
                            <div class="d-flex flex-stack">
                                <h3 class="text-dark">Item Sesuai</h3>
                            </div>
                            <div class="text-dark fs-1 fw-bold">{{ $stockOpname->details->where('selisih', 0)->count() }}
                            </div>
                            <div class="d-flex flex-column mt-2">
                                <span class="text-dark fw-semibold">Stok sistem sesuai dengan stok fisik</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card bg-light-warning card-xl-stretch mb-5">
                        <div class="card-body my-3">
                            <div class="d-flex flex-stack">
                                <h3 class="text-dark">Item Kurang</h3>
                            </div>
                            <div class="text-dark fs-1 fw-bold">
                                {{ $stockOpname->details->where('selisih', '<', 0)->count() }}</div>
                            <div class="d-flex flex-column mt-2">
                                <span class="text-dark fw-semibold">Stok fisik kurang dari stok sistem</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card bg-light-primary card-xl-stretch mb-5">
                        <div class="card-body my-3">
                            <div class="d-flex flex-stack">
                                <h3 class="text-dark">Item Lebih</h3>
                            </div>
                            <div class="text-dark fs-1 fw-bold">
                                {{ $stockOpname->details->where('selisih', '>', 0)->count() }}</div>
                            <div class="d-flex flex-column mt-2">
                                <span class="text-dark fw-semibold">Stok fisik lebih dari stok sistem</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Table -->
            <div class="mb-0">
                <h3 class="fw-bold fs-4 mb-4">Daftar Obat</h3>
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="details_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Obat</th>
                                <th>Satuan</th>
                                <th>Lokasi</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Selisih</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($stockOpname->details as $detail)
                                <tr>
                                    <td>{{ $detail->obat->nama_obat }}</td>
                                    <td>{{ $detail->satuan->nama }}</td>
                                    <td>{{ $detail->lokasi->nama }}</td>
                                    <td>{{ number_format($detail->stok_sistem, 2) }}</td>
                                    <td>{{ number_format($detail->stok_fisik, 2) }}</td>
                                    <td>
                                        @if ($detail->selisih > 0)
                                            <span
                                                class="badge badge-light-success">+{{ number_format($detail->selisih, 2) }}</span>
                                        @elseif($detail->selisih < 0)
                                            <span
                                                class="badge badge-light-danger">{{ number_format($detail->selisih, 2) }}</span>
                                        @else
                                            <span class="badge badge-light-primary">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">Belum ada data obat</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#details_table').DataTable({
                "language": {
                    "lengthMenu": "Show _MENU_",
                },
                "dom": "<'row'" +
                    "<'col-sm-6 d-flex align-items-center justify-content-start'l>" +
                    "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
                    ">" +
                    "<'table-responsive'tr>" +
                    "<'row'" +
                    "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                    "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                    ">"
            });
        });
    </script>
@endsection
