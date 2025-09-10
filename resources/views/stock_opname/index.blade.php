@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Stock Opname</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>Tambah Stock Opname
                </a>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="stockOpnameTable">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>No.</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="deleteModalLabel">Konfirmasi Hapus</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus stock opname ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Set up CSRF token for all AJAX requests with stronger configuration
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 419) {
                        toastr.error('Sesi habis, silahkan refresh halaman');
                        console.error('CSRF token mismatch. Page will refresh in 3 seconds.');
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                }
            });

            // Initialize DataTable
            var table = $('#stockOpnameTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('stock_opname.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'kode',
                        name: 'kode',
                        render: function(data, type, row) {
                            return '<span class="fw-bold">' + data + '</span>';
                        }
                    },
                    {
                        data: 'tanggal_formatted',
                        name: 'tanggal'
                    },
                    {
                        data: 'petugas',
                        name: 'user.name'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        render: function(data, type, row) {
                            return data;
                        }
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        render: function(data, type, row) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    "lengthMenu": "Show _MENU_ entries",
                    "zeroRecords": "No data available",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "search": "Search:",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                order: [
                    [2, 'desc']
                ]
            });

            // Handle delete button click
            var deleteId;

            $('#stockOpnameTable').on('click', '.btn-delete', function() {
                deleteId = $(this).data('id');
                $('#deleteModal').modal('show');
            });

            // Handle confirm delete button click
            $('#confirmDelete').click(function() {
                // Show loading state on button
                const $btn = $(this);
                $btn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...'
                    ).prop('disabled', true);

                // Use AJAX for deletion instead of form submission
                $.ajax({
                    url: "{{ route('stock_opname.destroy', ':id') }}".replace(':id', deleteId),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#deleteModal').modal('hide');

                        // Show success message
                        toastr.success('Stock opname berhasil dihapus');

                        // Reload the DataTable to reflect the changes
                        $('#stockOpnameTable').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        // Reset button state
                        $btn.html('Hapus').prop('disabled', false);

                        // Show error message
                        let errorMessage = 'Gagal menghapus stock opname';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        toastr.error(errorMessage);

                        // Special handling for CSRF token mismatch
                        if (xhr.status === 419) {
                            toastr.error(
                                'CSRF token mismatch. Halaman akan di-refresh dalam 3 detik.'
                                );
                            setTimeout(function() {
                                location.reload();
                            }, 3000);
                        }
                    },
                    complete: function() {
                        // Hide modal regardless of success/error
                        $('#deleteModal').modal('hide');
                    }
                });
            });
        });
    </script>
@endpush
