@extends('layout.app')

@section('title', 'Laporan Stok Obat')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">Laporan Stok Obat</h1>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-5">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                                class="path2"></span></i>
                        <input type="text" data-kt-docs-table-filter="search"
                            class="form-control form-control-solid w-250px ps-15" placeholder="Cari Stok Obat" />
                    </div>
                    <!--end::Search-->
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="stokObatTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Obat</th>
                                <th>Nama Obat</th>
                                <th>Golongan</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var table
        $(document).ready(function() {
            table = $('#stokObatTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('laporan.stok-obat.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode_obat',
                        name: 'obat.kode_obat'
                    },
                    {
                        data: 'nama_obat',
                        name: 'obat.nama_obat'
                    },
                    {
                        data: 'golongan',
                        name: 'obat.golongan.nama'
                    },
                    {
                        data: 'kategori',
                        name: 'obat.kategori.nama'
                    },
                    {
                        data: 'satuan',
                        name: 'obat.satuan.nama'
                    },
                    {
                        data: 'stok',
                        name: 'stok',
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false
                    },
                ],

            });

            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();
            });
        });
    </script>
@endpush
