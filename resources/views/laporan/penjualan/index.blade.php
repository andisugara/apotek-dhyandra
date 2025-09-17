@extends('layout.app')
@section('title', 'Laporan Penjualan')

@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .text-success {
            color: #50CD89 !important;
        }

        .text-danger {
            color: #F1416C !important;
        }

        .summary-card {
            border-radius: 8px;
            box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem 1rem rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h3 class="fw-bold">Laporan Penjualan</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Filter form-->
            <form action="{{ route('laporan.penjualan.index') }}" method="GET" id="filter-form" class="mb-8">
                <div class="row mb-5">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" />
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="ki-duotone ki-filter fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Filter
                        </button>

                        <a href="{{ route('laporan.penjualan.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-danger" target="_blank">
                            <i class="ki-duotone ki-file-down fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Export PDF
                        </a>
                    </div>
                </div>
            </form>
            <!--end::Filter form-->

            <!--begin::Summary cards-->
            @if (isset($summary))
                <div class="row g-5 g-xl-8 mb-8">
                    <div class="col-xl-4">
                        <div class="card bg-light-primary summary-card h-100">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between mb-7">
                                        <div class="me-2">
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total Penjualan</span>
                                            <span class="text-muted fw-semibold fs-6">Nilai total penjualan pada
                                                periode</span>
                                        </div>
                                        <span class="fw-bold text-primary fs-3x">Rp
                                            {{ number_format($summary->total_penjualan ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex flex-center h-80px w-80px mb-5">
                                        <i class="ki-duotone ki-dollar text-primary fs-3x">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card bg-light-warning summary-card h-100">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between mb-7">
                                        <div class="me-2">
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total HPP</span>
                                            <span class="text-muted fw-semibold fs-6">Nilai harga pokok penjualan</span>
                                        </div>
                                        <span class="fw-bold text-warning fs-3x">Rp
                                            {{ number_format($summary->total_hpp ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex flex-center h-80px w-80px mb-5">
                                        <i class="ki-duotone ki-tag text-warning fs-3x">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card bg-light-success summary-card h-100">
                            <div class="card-body">
                                <div class="d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between mb-7">
                                        <div class="me-2">
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total Keuntungan</span>
                                            <span class="text-muted fw-semibold fs-6">Keuntungan dari penjualan</span>
                                        </div>
                                        <span class="fw-bold text-success fs-3x">Rp
                                            {{ number_format($summary->total_keuntungan ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex flex-center h-80px w-80px mb-5">
                                        <i class="ki-duotone ki-chart-line-star text-success fs-3x">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!--end::Summary cards-->

            <!--begin::Table container-->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="penjualan_table">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th>No</th>
                            <th>No. Faktur</th>
                            <th>Tanggal</th>
                            <th>Nama Obat</th>
                            <th>Satuan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Jumlah</th>
                            <th>Diskon</th>
                            <th>PPN</th>
                            <th>Total</th>
                            <th>Keuntungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi oleh DataTables -->
                    </tbody>
                </table>
            </div>
            <!--end::Table container-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#penjualan_table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100, // Menampilkan 100 data per halaman
                ajax: {
                    url: "{{ route('laporan.penjualan.index') }}",
                    data: function(d) {
                        d.start_date = $('input[name="start_date"]').val();
                        d.end_date = $('input[name="end_date"]').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'no_faktur',
                        name: 'penjualans.no_faktur'
                    },
                    {
                        data: 'tanggal_formatted',
                        name: 'penjualans.tanggal_penjualan'
                    },
                    {
                        data: 'nama_obat',
                        name: 'obat.nama_obat'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'harga_beli_formatted',
                        name: 'penjualan_details.harga_beli',
                        searchable: false
                    },
                    {
                        data: 'harga_jual_formatted',
                        name: 'penjualan_details.harga',
                        searchable: false
                    },
                    {
                        data: 'jumlah',
                        name: 'penjualan_details.jumlah'
                    },
                    {
                        data: 'diskon_formatted',
                        name: 'penjualan_details.diskon',
                        searchable: false
                    },
                    {
                        data: 'ppn_formatted',
                        name: 'penjualan_details.ppn',
                        searchable: false
                    },
                    {
                        data: 'total_formatted',
                        name: 'penjualan_details.total',
                        searchable: false
                    },
                    {
                        data: 'keuntungan_formatted',
                        name: 'keuntungan',
                        searchable: false
                    }
                ],
                order: [
                    [2, 'desc']
                ], // Default sort by tanggal
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    zeroRecords: "Tidak ada data yang ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Filter form submit handler
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();

                // Refresh summary data
                $.ajax({
                    url: "{{ route('laporan.penjualan.index') }}",
                    data: {
                        start_date: $('input[name="start_date"]').val(),
                        end_date: $('input[name="end_date"]').val(),
                        _token: "{{ csrf_token() }}",
                        summary_only: true
                    },
                    method: 'GET',
                    success: function(response) {
                        // Update summary cards if response has summary data
                        if (response.summary) {
                            $('.total_penjualan').text('Rp ' + formatNumber(response.summary
                                .total_penjualan));
                            $('.total_hpp').text('Rp ' + formatNumber(response.summary
                                .total_hpp));
                            $('.total_keuntungan').text('Rp ' + formatNumber(response.summary
                                .total_keuntungan));
                        }
                    }
                });
            });

            function formatNumber(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }
        });
    </script>
@endpush
