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

            <div class="d-flex justify-content-end mb-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Cari Penjualan" />
                </div>
                <!--end::Search-->
            </div>

            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="penjualanTable">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th>No</th>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Pasien</th>
                            <th>Jenis</th>
                            <th>Total</th>
                            <th>User</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi oleh DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@include('penjualan.delete_modal')

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#penjualanTable').DataTable({
                processing: true,
                serverSide: true,

                ajax: "{{ route('penjualan.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'no_faktur',
                        name: 'no_faktur'
                    },
                    {
                        data: 'tanggal_formatted',
                        name: 'tanggal_penjualan'
                    },
                    {
                        data: 'pasien_nama',
                        name: 'pasien.nama'
                    },
                    {
                        data: 'jenis_display',
                        name: 'jenis'
                    },
                    {
                        data: 'grand_total_formatted',
                        name: 'grand_total'
                    },
                    {
                        data: 'user.name',
                        name: 'user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    },
                ],
                order: [
                    [1, 'desc']
                ], // Default sort by no faktur desc
            });

            // Search functionality
            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var url = "{{ route('penjualan.destroy', ':id') }}".replace(':id', id);
                $('#deleteForm').attr('action', url);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
