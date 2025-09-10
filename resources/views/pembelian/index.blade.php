@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Data Pembelian</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('pembelian.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>Tambah Pembelian
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="pembelianTable">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Jenis</th>
                        <th>Grand Total</th>
                        <th>Status Jatuh Tempo</th>
                        <th>Status Pembayaran</th>
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
                    <h5 class="modal-title">Hapus Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin menghapus data pembelian ini?</p>
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
        $(document).ready(function() {
            var table = $('#pembelianTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('pembelian.index') }}",
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
                        data: 'tanggal_faktur_formatted',
                        name: 'tanggal_faktur'
                    },
                    {
                        data: 'supplier_nama',
                        name: 'supplier_id'
                    },
                    {
                        data: 'jenis_badge',
                        name: 'jenis'
                    },
                    {
                        data: 'grand_total_formatted',
                        name: 'grand_total'
                    },
                    {
                        data: 'status_jatuh_tempo',
                        name: 'tanggal_jatuh_tempo',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_pembayaran_formatted',
                        name: 'status_pembayaran',
                        orderable: true,
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

            // Delete confirmation
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var deleteUrl = '{{ route('pembelian.destroy', ':id') }}';
                deleteUrl = deleteUrl.replace(':id', id);
                $('#deleteForm').attr('action', deleteUrl);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
