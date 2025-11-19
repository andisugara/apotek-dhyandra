@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sinkronisasi Penjualan</h3>
                <div class="card-toolbar">
                    <span id="connection-status" class="badge badge-secondary">
                        <i class="fa fa-spinner fa-spin"></i> Checking...
                    </span>
                </div>
            </div>
            <div class="card-body">
                {{-- Stats --}}
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="card bg-light-warning h-100">
                            <div class="card-body">
                                <h2 class="text-warning">{{ $offlineCount }}</h2>
                                <p class="mb-0">Penjualan Offline (Belum di-push)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light-success h-100">
                            <div class="card-body">
                                <h2 class="text-success">{{ $onlineCount }}</h2>
                                <p class="mb-0">Penjualan Online (Sudah tersinkron)</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <button id="btn-push" class="btn btn-warning btn-lg w-100"
                            {{ $offlineCount == 0 ? 'disabled' : '' }}>
                            <i class="ki-outline ki-cloud-add fs-2"></i>
                            Push Penjualan ke Server ({{ $offlineCount }})
                        </button>
                        <small class="text-muted d-block mt-2">
                            Upload penjualan offline ke server pusat
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button id="btn-pull" class="btn btn-primary btn-lg w-100">
                            <i class="ki-outline ki-cloud-download fs-2"></i>
                            Pull Penjualan dari Server
                        </button>
                        <small class="text-muted d-block mt-2">
                            Download penjualan online dari server
                        </small>
                    </div>
                </div>

                {{-- Progress --}}
                <div id="sync-progress" class="alert alert-info mt-4" style="display: none;">
                    <strong>Sedang sinkronisasi...</strong>
                    <div class="progress mt-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                </div>

                {{-- Result --}}
                <div id="sync-result" class="mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Check connection on load
            checkConnection();

            // Auto-check every 30 seconds
            setInterval(checkConnection, 30000);

            // Push button
            $('#btn-push').click(function() {
                if (!confirm('Push {{ $offlineCount }} penjualan offline ke server?')) return;

                const btn = $(this);
                const originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Pushing...');
                $('#sync-progress').show();
                $('#sync-result').hide();

                $.ajax({
                    url: '{{ route('sync.push') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#sync-progress').hide();
                        btn.prop('disabled', false).html(originalText);

                        $('#sync-result')
                            .removeClass('alert-danger')
                            .addClass('alert alert-success')
                            .html(`
                        <h5>✓ Push Berhasil</h5>
                        <p>${response.message}</p>
                        <ul>
                            <li>Berhasil: ${response.data.success}</li>
                            <li>Gagal: ${response.data.failed}</li>
                        </ul>
                    `)
                            .show();

                        if (response.data.success > 0) {
                            setTimeout(() => location.reload(), 2000);
                        }
                    },
                    error: function(xhr) {
                        $('#sync-progress').hide();
                        btn.prop('disabled', false).html(originalText);

                        $('#sync-result')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .html(`
                        <h5>✗ Push Gagal</h5>
                        <p>${xhr.responseJSON?.message || 'Terjadi kesalahan'}</p>
                    `)
                            .show();
                    }
                });
            });

            // Pull button
            $('#btn-pull').click(function() {
                if (!confirm('Pull penjualan online dari server?')) return;

                const btn = $(this);
                const originalText = btn.html();

                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Pulling...');
                $('#sync-progress').show();
                $('#sync-result').hide();

                $.ajax({
                    url: '{{ route('sync.pull') }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#sync-progress').hide();
                        btn.prop('disabled', false).html(originalText);

                        $('#sync-result')
                            .removeClass('alert-danger')
                            .addClass('alert alert-success')
                            .html(`
                        <h5>✓ Pull Berhasil</h5>
                        <p>${response.message}</p>
                        <ul>
                            <li>Berhasil: ${response.data.success}</li>
                            <li>Duplikat (Skip): ${response.data.skipped}</li>
                        </ul>
                    `)
                            .show();

                        if (response.data.success > 0) {
                            setTimeout(() => location.reload(), 2000);
                        }
                    },
                    error: function(xhr) {
                        $('#sync-progress').hide();
                        btn.prop('disabled', false).html(originalText);

                        $('#sync-result')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .html(`
                        <h5>✗ Pull Gagal</h5>
                        <p>${xhr.responseJSON?.message || 'Terjadi kesalahan'}</p>
                    `)
                            .show();
                    }
                });
            });

            function checkConnection() {
                $.get('{{ route('sync.check') }}', function(response) {
                    const badge = $('#connection-status');

                    if (response.is_online) {
                        badge.removeClass('badge-secondary badge-danger')
                            .addClass('badge-success')
                            .html('<i class="ki-outline ki-check-circle"></i> Online');
                        $('#btn-push, #btn-pull').prop('disabled', false);
                    } else {
                        badge.removeClass('badge-secondary badge-success')
                            .addClass('badge-danger')
                            .html('<i class="ki-outline ki-cross-circle"></i> Offline');
                        $('#btn-push, #btn-pull').prop('disabled', true);
                    }
                }).fail(function() {
                    $('#connection-status')
                        .removeClass('badge-secondary badge-success')
                        .addClass('badge-danger')
                        .html('<i class="ki-outline ki-cross-circle"></i> Offline');
                    $('#btn-push, #btn-pull').prop('disabled', true);
                });
            }
        });
    </script>
@endpush
