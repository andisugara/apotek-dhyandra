@extends('layout.app')
@section('title', 'Daftar Pengeluaran')

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h2>Daftar Pengeluaran</h2>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <!--begin::Add user-->
                    <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>Tambah Pengeluaran
                    </a>
                    <!--end::Add user-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Filter-->
            <div class="card mb-6">
                <div class="card-header border-0">
                    <div class="card-title">
                        <h3 class="card-label">Filter Data</h3>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form id="filter-form" class="form">
                        <div class="row mb-6">
                            <div class="col-lg-3 fv-row">
                                <label class="col-form-label fw-bold fs-6">Bulan:</label>
                                <select class="form-select form-select-solid" id="month" name="month"
                                    data-control="select2" data-placeholder="Pilih Bulan">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-3 fv-row">
                                <label class="col-form-label fw-bold fs-6">Tahun:</label>
                                <select class="form-select form-select-solid" id="year" name="year"
                                    data-control="select2" data-placeholder="Pilih Tahun">
                                    @for ($i = $currentYear - 5; $i <= $currentYear; $i++)
                                        <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-3">
                                    <span class="indicator-label">Filter</span>
                                </button>
                                <button type="button" id="reset-filter" class="btn btn-light">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--end::Filter-->

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1 ">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span
                            class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Cari Supplier" />
                </div>
                <!--end::Search-->
            </div>
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="pengeluaran-table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th class="text-end">Jumlah</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus data pengeluaran ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var table;
        $(document).ready(function() {
            // Initialize DataTable with filter
            table = $('#pengeluaran-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('pengeluaran.index') }}",
                    data: function(d) {
                        d.month = $('#month').val();
                        d.year = $('#year').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'formatted_tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'formatted_jumlah',
                        name: 'jumlah',
                        className: 'text-end'
                    },
                    {
                        data: 'user_name',
                        name: 'user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ], // Order by date desc by default
                language: {
                    processing: "Sedang memproses...",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
                    infoPostFix: "",
                    loadingRecords: "Memuat data...",
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    emptyTable: "Tidak ada data tersedia pada tabel ini",
                    paginate: {
                        first: "Pertama",
                        previous: "Sebelumnya",
                        next: "Selanjutnya",
                        last: "Terakhir"
                    },
                    aria: {
                        sortAscending: ": aktifkan untuk mengurutkan kolom ke atas",
                        sortDescending: ": aktifkan untuk mengurutkan kolom ke bawah"
                    }
                }
            });

            const filterSearch = document.querySelector('[data-kt-docs-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                table.search(e.target.value).draw();
            });

            // Handle filter form submission
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Reset filter
            $('#reset-filter').on('click', function() {
                $('#month').val({{ $currentMonth }});
                $('#year').val({{ $currentYear }});
                table.ajax.reload();
            });

            // Delete confirmation
            $('#pengeluaran-table').on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                $('#deleteForm').attr('action', `{{ url('pengeluaran') }}/${id}`);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
