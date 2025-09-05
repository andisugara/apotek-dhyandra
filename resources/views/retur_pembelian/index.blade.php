@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Daftar Retur Pembelian</h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('retur_pembelian.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>Tambah Retur
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="retur-table">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-100px">No Retur</th>
                            <th class="min-w-100px">Tanggal Retur</th>
                            <th class="min-w-100px">No Faktur</th>
                            <th class="min-w-100px">Supplier</th>
                            <th class="min-w-100px">Total</th>
                            <th class="min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus retur pembelian ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="delete-form" method="POST">
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
            // Initialize DataTable
            var table = $('#retur-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('retur_pembelian.index') }}",
                columns: [{
                        data: 'no_retur',
                        name: 'no_retur'
                    },
                    {
                        data: 'tanggal_retur_formatted',
                        name: 'tanggal_retur'
                    },
                    {
                        data: 'no_faktur',
                        name: 'no_faktur'
                    },
                    {
                        data: 'supplier_nama',
                        name: 'supplier_nama'
                    },
                    {
                        data: 'grand_total_formatted',
                        name: 'grand_total'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'desc']
                ]
            });

            // Delete confirmation
            $('#retur-table').on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var url = "{{ route('retur_pembelian.destroy', ':id') }}".replace(':id', id);
                $('#delete-form').attr('action', url);
                $('#deleteModal').modal('show');
            });
        });
    </script>
@endpush
