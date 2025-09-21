@extends('layout.app')
@section('title', 'Laporan Pembelian')

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

        td.dt-control {
            background: url('{{ asset('assets/media/icons/plus.png') }}') no-repeat center center;
            background-size: 20px;
            cursor: pointer;
        }

        tr.shown td.dt-control {
            background: url('{{ asset('assets/media/icons/minus.png') }}') no-repeat center center;
            background-size: 20px;
        }

        .child-table {
            width: 100%;
            margin: 10px 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .child-table th,
        .child-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #E4E6EF;
        }

        .child-table thead th {
            background-color: #F5F8FA;
            font-weight: 600;
            color: #3F4254;
        }

        .child-table tbody tr:hover {
            background-color: #F9F9F9;
        }

        .detail-row {
            background-color: #F5F8FA;
            padding: 15px;
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
                <h3 class="fw-bold">Laporan Pembelian</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Filter form-->
            <form action="{{ route('laporan.pembelian.index') }}" method="GET" id="filter-form" class="mb-8">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Jenis Pembayaran</label>
                            <select class="form-select" name="jenis_pembayaran">
                                <option value="" {{ !isset($jenisPembayaran) ? 'selected' : '' }}>Semua</option>
                                <option value="TUNAI"
                                    {{ isset($jenisPembayaran) && $jenisPembayaran == 'TUNAI' ? 'selected' : '' }}>Tunai
                                </option>
                                <option value="HUTANG"
                                    {{ isset($jenisPembayaran) && $jenisPembayaran == 'HUTANG' ? 'selected' : '' }}>Hutang
                                </option>
                                <option value="KONSINYASI"
                                    {{ isset($jenisPembayaran) && $jenisPembayaran == 'KONSINYASI' ? 'selected' : '' }}>
                                    Konsinyasi</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Supplier</label>
                            <select class="form-select " name="supplier_id">
                                <option value="">Semua Supplier</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ isset($supplierId) && $supplierId == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Obat</label>
                            <select class="form-select" name="obat_id">
                                <option value="">Semua Obat</option>
                                @foreach ($obats as $obat)
                                    <option value="{{ $obat->id }}"
                                        {{ isset($obatId) && $obatId == $obat->id ? 'selected' : '' }}>
                                        {{ $obat->nama_obat }}
                                    </option>
                                @endforeach
                            </select>
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

                        <a href="{{ route('laporan.pembelian.pdf', [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'jenis_pembayaran' => $jenisPembayaran ?? '',
                            'supplier_id' => $supplierId ?? '',
                            'obat_id' => $obatId ?? '',
                        ]) }}"
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
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total Pembelian</span>
                                            <span class="text-muted fw-semibold fs-6">Nilai total pembelian pada
                                                periode</span>
                                        </div>
                                        <span class="fw-bold text-primary fs-3x">Rp
                                            {{ number_format($summary->total_pembelian ?? 0, 0, ',', '.') }}</span>
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
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total Diskon</span>
                                            <span class="text-muted fw-semibold fs-6">Nilai diskon yang diberikan</span>
                                        </div>
                                        <span class="fw-bold text-warning fs-3x">Rp
                                            {{ number_format($summary->total_diskon ?? 0, 0, ',', '.') }}</span>
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
                                            <span class="text-dark fw-bold fs-3 d-block mb-1">Total Faktur</span>
                                            <span class="text-muted fw-semibold fs-6">Jumlah transaksi pembelian</span>
                                        </div>
                                        <span class="fw-bold text-success fs-3x">
                                            {{ number_format($summary->total_faktur ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex flex-center h-80px w-80px mb-5">
                                        <i class="ki-duotone ki-document text-success fs-3x">
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
            <div class="d-flex justify-content-end mb-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Cari Pembelian" />
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Table container-->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="pembelian_table">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th></th>
                            <th>No</th>
                            <th>No. PO</th>
                            <th>No. Faktur</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Subtotal</th>
                            <th>Diskon</th>
                            <th>PPN</th>
                            <th>Grand Total</th>
                            <th>Jatuh Tempo</th>
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
        /* Formatting function for row details */
        function formatDetailRow(details) {
            let html = '<div class="p-4">' +
                '<h5 class="mb-3">Detail Pembelian</h5>' +
                '<table class="child-table table table-row-bordered table-striped">' +
                '<thead class="fw-bold bg-light">' +
                '<tr>' +
                '<th>Obat</th>' +
                '<th>Satuan</th>' +
                '<th>Jumlah</th>' +
                '<th>Harga Beli</th>' +
                '<th>Subtotal</th>' +
                '<th>Diskon %</th>' +
                '<th>Diskon Rp</th>' +
                '<th>HPP</th>' +
                '<th>Margin %</th>' +
                '<th>Harga Jual</th>' +
                '<th>No Batch</th>' +
                '<th>Expired</th>' +
                '<th>Total</th>' +
                '</tr>' +
                '</thead>' +
                '<tbody>';

            // Check if details is an array and has items
            if (!Array.isArray(details) || details.length === 0) {
                html += '<tr><td colspan="13" class="text-center p-5">Tidak ada detail pembelian ditemukan</td></tr>';
            } else {
                details.forEach(function(item) {
                    const subtotal = item.harga_beli * item.jumlah;
                    const margin = item.harga_jual_per_unit > 0 && item.hpp_per_unit > 0 ?
                        ((item.harga_jual_per_unit - item.hpp_per_unit) / item.hpp_per_unit * 100).toFixed(2) : 0;

                    html += '<tr>' +
                        '<td>' + (item.nama_obat || '-') + '</td>' +
                        '<td>' + (item.satuan || '-') + '</td>' +
                        '<td>' + (item.jumlah || 0) + '</td>' +
                        '<td>Rp ' + formatNumber(item.harga_beli || 0) + '</td>' +
                        '<td>Rp ' + formatNumber(subtotal || 0) + '</td>' +
                        '<td>' + (item.diskon_persen || 0) + '%</td>' +
                        '<td>Rp ' + formatNumber(item.diskon_nominal || 0) + '</td>' +
                        '<td>Rp ' + formatNumber(item.hpp_per_unit || 0) + '</td>' +
                        '<td>' + margin + '%</td>' +
                        '<td>Rp ' + formatNumber(item.harga_jual_per_unit || 0) + '</td>' +
                        '<td>' + (item.no_batch || '-') + '</td>' +
                        '<td>' + (item.tanggal_expired ? new Date(item.tanggal_expired).toLocaleDateString(
                            'id-ID') : '-') + '</td>' +
                        '<td>Rp ' + formatNumber(item.total || 0) + '</td>' +
                        '</tr>';
                });
            }

            html += '</tbody></table></div>';
            return html;
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('[name="supplier_id"]').select2({
                placeholder: "Pilih...",
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });

            $('[name="obat_id"]').select2({
                placeholder: "Pilih...",
                allowClear: true,
                width: '100%',
                dropdownAutoWidth: true,
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    return data.text;
                },
                templateSelection: function(data) {
                    return data.text;
                }
            });

            // Initialize DataTable with expandable rows
            var table = $('#pembelian_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('laporan.pembelian.index') }}",
                    data: function(d) {
                        d.start_date = $('input[name="start_date"]').val();
                        d.end_date = $('input[name="end_date"]').val();
                        d.jenis_pembayaran = $('select[name="jenis_pembayaran"]').val();
                        d.supplier_id = $('select[name="supplier_id"]').val();
                        d.obat_id = $('select[name="obat_id"]').val();
                    }
                },
                columns: [{
                        className: 'dt-control',
                        orderable: false,
                        searchable: false,
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'no_po',
                        name: 'pembelian.no_po',
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'no_faktur',
                        name: 'pembelian.no_faktur'
                    },
                    {
                        data: 'tanggal_formatted',
                        name: 'pembelian.tanggal_faktur'
                    },
                    {
                        data: 'supplier',
                        name: 'suppliers.nama',
                        searchable: true
                    },
                    {
                        data: 'jenis_formatted',
                        name: 'pembelian.jenis',
                        searchable: true
                    },
                    {
                        data: 'status_pembayaran_formatted',
                        name: 'pembelian.status_pembayaran',
                        searchable: true
                    },
                    {
                        data: 'subtotal_formatted',
                        name: 'pembelian.subtotal',
                        searchable: false
                    },
                    {
                        data: 'diskon_formatted',
                        name: 'pembelian.diskon_total',
                        searchable: false
                    },
                    {
                        data: 'ppn_formatted',
                        name: 'pembelian.ppn_total',
                        searchable: false
                    },
                    {
                        data: 'grand_total_formatted',
                        name: 'pembelian.grand_total',
                        searchable: false
                    },
                    {
                        data: 'jatuh_tempo_formatted',
                        name: 'pembelian.tanggal_jatuh_tempo',
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc']
                ], // Default sort by tanggal
                language: {
                    zeroRecords: "Tidak ada data yang ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ total entri)",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Add event listener for opening and closing details
            $('#pembelian_table tbody').on('click', 'td.dt-control', function() {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Close all other open rows first
                    table.rows().every(function() {
                        if (this.child.isShown()) {
                            this.child.hide();
                            $(this.node()).removeClass('shown');
                        }
                    });

                    // Show loading indicator
                    row.child(
                        '<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Loading details...</div>'
                    ).show();
                    tr.addClass('shown');

                    // Fix the URL by replacing &amp; with & if present
                    let detailsUrl = row.data().details_url;
                    if (detailsUrl && detailsUrl.includes('&amp;')) {
                        detailsUrl = detailsUrl.replace(/&amp;/g, '&');
                    }

                    // Open this row and fetch details via AJAX
                    $.ajax({
                        url: detailsUrl,
                        type: 'GET',
                        dataType: 'json',
                        success: function(details) {
                            row.child(formatDetailRow(details)).show();
                            // Ensure we keep the shown class
                            tr.addClass('shown');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading details:', error, xhr.responseText);

                            row.child(
                                    '<div class="text-center py-4 text-danger">Error loading details: ' +
                                    error + '</div>')
                                .show();
                        }
                    });
                }
            });

            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();
            });

            // Filter form submit handler
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();

                // Refresh summary data
                $.ajax({
                    url: "{{ route('laporan.pembelian.index') }}",
                    data: {
                        start_date: $('input[name="start_date"]').val(),
                        end_date: $('input[name="end_date"]').val(),
                        jenis_pembayaran: $('select[name="jenis_pembayaran"]').val(),
                        supplier_id: $('select[name="supplier_id"]').val(),
                        obat_id: $('select[name="obat_id"]').val(),
                        _token: "{{ csrf_token() }}",
                        summary_only: true
                    },
                    method: 'GET',
                    success: function(response) {
                        // Update summary cards if response has summary data
                        if (response.summary) {
                            $('.total_pembelian').text('Rp ' + formatNumber(response.summary
                                .total_pembelian));
                            $('.total_diskon').text('Rp ' + formatNumber(response.summary
                                .total_diskon));
                            $('.total_faktur').text(formatNumber(response.summary
                                .total_faktur));
                        }
                    }
                });
            });
        });
    </script>
@endpush
