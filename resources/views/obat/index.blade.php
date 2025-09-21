@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Data Obat</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('obat.import') }}" class="btn btn-success me-2">
                        <i class="ki-duotone ki-file-up fs-2"></i>Import Obat
                    </a>
                    <a href="{{ route('obat.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>Tambah Obat
                    </a>
                </div>
            </div>

        </div>
        <div class="card-body py-4">
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
            <div class="d-flex justify-content-end">

                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1 ">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Search Customers" />
                </div>
                <!--end::Search-->
            </div>
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="obatTable">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Pabrik</th>
                        <th>Golongan</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    <!-- Data akan diisi oleh DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin menghapus data obat ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var table
        $(document).ready(function() {
            table = $('#obatTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('obat.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode_obat',
                        name: 'kode_obat'
                    },
                    {
                        data: 'nama_obat',
                        name: 'nama_obat'
                    },
                    {
                        data: 'nama_pabrik',
                        name: 'pabrik_id'
                    },
                    {
                        data: 'nama_golongan',
                        name: 'golongan_id'
                    },
                    {
                        data: 'nama_kategori',
                        name: 'kategori_id'
                    },
                    {
                        data: 'status_label',
                        name: 'is_active',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();
            });

            // Delete confirmation
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var deleteUrl = '{{ route('obat.destroy', ':id') }}';
                deleteUrl = deleteUrl.replace(':id', id);
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
