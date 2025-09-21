@extends('layout.app')

@section('title', 'Laporan Stok Obat Expired')

@push('styles')
    <style>
        .btn-clear-search {
            cursor: pointer;
            z-index: 100;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-clear-search i {
            font-size: 0.85rem !important;
            color: #7e8299;
        }

        .btn-clear-search:hover i {
            color: #009ef7;
        }
    </style>
@endpush

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title d-flex flex-column flex-md-row">
                <h2 class="mb-3 mb-md-0">Laporan Stok Obat Expired</h2>

            </div>
            <!--end::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <!--begin::Filter-->
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-filter fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Filter Status
                    </button>
                    <!--begin::Menu 1-->
                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"
                        id="kt-toolbar-filter">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="fs-4 text-dark fw-bold">Filter Opsi</div>
                        </div>
                        <!--end::Header-->

                        <!--begin::Separator-->
                        <div class="separator border-gray-200"></div>
                        <!--end::Separator-->

                        <!--begin::Content-->
                        <div class="px-7 py-5">
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <!--begin::Label-->
                                <label class="form-label fs-5 fw-semibold mb-3">Status Expired:</label>
                                <!--end::Label-->

                                <!--begin::Options-->
                                <div class="d-flex flex-column flex-wrap fw-semibold" data-kt-user-table-filter="status">
                                    <!--begin::Option-->
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="status_filter" value="all"
                                            checked="checked" />
                                        <span class="form-check-label text-gray-600">Semua</span>
                                    </label>
                                    <!--end::Option-->

                                    <!--begin::Option-->
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="status_filter"
                                            value="expired" />
                                        <span class="form-check-label text-gray-600">Sudah Expired</span>
                                    </label>
                                    <!--end::Option-->

                                    <!--begin::Option-->
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="status_filter"
                                            value="soon" />
                                        <span class="form-check-label text-gray-600">Segera Expired (30 Hari)</span>
                                    </label>
                                    <!--end::Option-->
                                </div>
                                <!--end::Options-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2"
                                    data-kt-menu-dismiss="true" data-kt-user-table-filter="reset">Reset</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true"
                                    data-kt-user-table-filter="filter">Terapkan</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Menu 1-->
                    <!--end::Filter-->

                    <!--begin::Export-->
                    <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_export_users">
                        <i class="ki-duotone ki-exit-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Export
                    </button>
                    <!--end::Export-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <div class="d-flex justify-content-end mt-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1 ms-md-4 mt-md-0 mt-2">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Cari nama/kode obat..." />
                    <span class="btn btn-icon btn-clear-search position-absolute end-0 me-3" style="display:none;">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </span>
                </div>
                <!--end::Search-->
            </div>
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="stok_expired_table">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nama Obat</th>
                        <th>Kode Obat</th>
                        <th>Satuan</th>
                        <th>Golongan</th>
                        <th>Kategori</th>
                        <th>No Batch</th>
                        <th>Expired Date</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600"></tbody>
            </table>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Modal - Export Data-->
    <div class="modal fade" id="kt_modal_export_users" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header">
                    <h2 class="fw-bold">Export Laporan Stok Expired</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-users-modal-action="close"
                        data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <!--end::Modal header-->

                <!--begin::Modal body-->
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <!--begin::Form-->
                    <form id="export_form" class="form" action="{{ route('laporan.stok_expired.export.pdf') }}"
                        method="get" target="_blank">
                        <!--begin::Input group-->
                        <div class="fv-row mb-10">
                            <label class="fs-6 fw-semibold form-label mb-2">Status Expired:</label>
                            <select name="status" class="form-select form-select-solid fw-bold"
                                data-placeholder="Pilih Status">
                                <option value="">Semua</option>
                                <option value="expired">Sudah Expired</option>
                                <option value="soon">Segera Expired (30 Hari)</option>
                            </select>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="text-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Export PDF</span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal - Export Data-->
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function() {
            var table = $('#stok_expired_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('laporan.stok_expired.index') }}",
                    data: function(d) {
                        d.status = $('input[name=status_filter]:checked').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama_obat',
                        name: 'obat.nama_obat'
                    },
                    {
                        data: 'kode_obat',
                        name: 'obat.kode_obat'
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'golongan',
                        name: 'golongan'
                    },
                    {
                        data: 'kategori',
                        name: 'kategori'
                    },
                    {
                        data: 'no_batch',
                        name: 'no_batch'
                    },
                    {
                        data: 'tanggal_expired_formatted',
                        name: 'tanggal_expired'
                    },
                    {
                        data: 'qty',
                        name: 'qty'
                    },
                    {
                        data: 'status_expired',
                        name: 'tanggal_expired'
                    },
                    {
                        data: 'lokasi',
                        name: 'obat.lokasi.nama'
                    }
                ],
                order: [
                    [7, 'asc']
                ] // Order by expired date ascending (soonest first)
            });

            // Search functionality
            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            const clearSearchBtn = document.querySelector('.btn-clear-search');

            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();

                // Show/hide clear button based on input content
                if (e.target.value !== '') {
                    clearSearchBtn.style.display = 'flex';
                } else {
                    clearSearchBtn.style.display = 'none';
                }
            });

            // Clear search when clicking the clear button
            clearSearchBtn.addEventListener('click', function() {
                filterSearch.value = '';
                table.search('').draw();
                clearSearchBtn.style.display = 'none';
            });

            // Re-draw table when filter is applied
            $('input[name=status_filter]').on('change', function() {
                table.draw();
            });

            // Reset filter
            $('[data-kt-user-table-filter="reset"]').on('click', function() {
                $('input[name=status_filter][value="all"]').prop('checked', true);
                filterSearch.value = ''; // Clear search input
                table.search('').draw(); // Clear search and redraw table
            });

            // Apply filter
            $('[data-kt-user-table-filter="filter"]').on('click', function() {
                table.draw();
            });
        });
    </script>
@endpush
