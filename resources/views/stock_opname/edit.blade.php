@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Edit Stock Opname</h3>
                <span class="text-gray-600 fs-7 ms-2">{{ $stockOpname->kode }}</span>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.index') }}" class="btn btn-secondary me-2">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
                <form method="POST" action="{{ route('stock_opname.complete', $stockOpname) }}" class="d-inline"
                    id="formComplete">
                    @csrf
                    @method('PUT')
                    <button type="button" class="btn btn-success" id="btnComplete">
                        <i class="ki-duotone ki-check-circle fs-2"></i>Selesaikan
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <!-- Info Card -->
            <div class="card shadow-sm mb-8">
                <div class="card-body">
                    <form action="{{ route('stock_opname.update', $stockOpname) }}" method="POST" id="formUpdate">
                        @csrf
                        @method('PUT')
                        <div class="row mb-5">
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="form-label fw-semibold">Kode Stock Opname</label>
                                    <input type="text" class="form-control form-control-solid"
                                        value="{{ $stockOpname->kode }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="form-label fw-semibold">Tanggal</label>
                                    <input type="date" class="form-control form-control-solid"
                                        value="{{ $stockOpname->tanggal->format('Y-m-d') }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="form-label fw-semibold">Status</label>
                                    <div class="form-control form-control-solid d-flex align-items-center">
                                        @if ($stockOpname->status == 'draft')
                                            <span class="badge badge-light-warning me-2">Draft</span>
                                        @else
                                            <span class="badge badge-light-success me-2">Selesai</span>
                                        @endif
                                        {{ ucfirst($stockOpname->status) }}
                                    </div>
                                    <input type="hidden" name="status" value="{{ $stockOpname->status }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label fw-semibold">Keterangan</label>
                                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                        placeholder="Keterangan stock opname">{{ old('keterangan', $stockOpname->keterangan) }}</textarea>
                                    @error('keterangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btnSimpan">
                                <i class="ki-duotone ki-save-2 fs-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tambah Obat Card -->
            <div class="card shadow-sm mb-8">
                <div class="card-header">
                    <h3 class="card-title">Tambah Obat</h3>
                </div>
                <div class="card-body">
                    <form id="formAddObat" onsubmit="return false;">
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Lokasi</label>
                                    <select id="lokasi_id" class="form-select" data-control="select2"
                                        data-placeholder="Pilih Lokasi">
                                        <option value=""></option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="fv-plugins-message-container invalid-feedback lokasi-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Obat</label>
                                    <select id="obat_id" class="form-select" data-control="select2"
                                        data-placeholder="Pilih Obat" disabled>
                                        <option value=""></option>
                                    </select>
                                    <div class="fv-plugins-message-container invalid-feedback obat-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 d-none" id="detail_container">
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Satuan</label>
                                    <select id="satuan_id" class="form-select" data-control="select2"
                                        data-placeholder="Pilih Satuan">
                                        <option value=""></option>
                                    </select>
                                    <div class="fv-plugins-message-container invalid-feedback satuan-error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="required form-label">No. Batch</label>
                                    <input type="text" id="no_batch" class="form-control" placeholder="Nomor batch" />
                                    <div class="fv-plugins-message-container invalid-feedback batch-error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Tanggal Expired</label>
                                    <input type="date" id="tanggal_expired" class="form-control" />
                                    <div class="fv-plugins-message-container invalid-feedback expired-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 d-none" id="stok_container">
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Stok Sistem</label>
                                    <div class="input-group">
                                        <input type="number" id="stok_sistem" class="form-control" min="0"
                                            readonly />
                                        <span class="input-group-text">Unit</span>
                                    </div>
                                    <div class="fv-plugins-message-container invalid-feedback sistem-error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Stok Fisik</label>
                                    <div class="input-group">
                                        <input type="number" id="stok_fisik" class="form-control" min="0" />
                                        <span class="input-group-text">Unit</span>
                                    </div>
                                    <div class="fv-plugins-message-container invalid-feedback fisik-error"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-5">
                                    <label class="form-label">Selisih</label>
                                    <div class="input-group">
                                        <input type="number" id="selisih" class="form-control" readonly />
                                        <span class="input-group-text">Unit</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 d-none" id="tindakan_container">
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label class="form-label">Tindakan</label>
                                    <select id="tindakan" class="form-select" data-control="select2"
                                        data-placeholder="Pilih Tindakan">
                                        <option value=""></option>
                                        <option value="Penyesuaian stok">Penyesuaian stok</option>
                                        <option value="Investigasi lebih lanjut">Investigasi lebih lanjut</option>
                                        <option value="Kehilangan">Kehilangan</option>
                                        <option value="Kadaluarsa">Kadaluarsa</option>
                                        <option value="Rusak">Rusak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-5">
                                    <label class="form-label">Catatan</label>
                                    <textarea id="catatan" class="form-control" rows="3" placeholder="Catatan tambahan"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" id="btn_tambah_obat">
                                <i class="ki-duotone ki-plus-square fs-2"></i>Tambah Obat
                                <span class="indicator-progress">Menambahkan...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Obat Card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Daftar Obat</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="details_table">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Obat</th>
                                    <th>Satuan</th>
                                    <th>Batch</th>
                                    <th>Expired</th>
                                    <th>Stok Sistem</th>
                                    <th>Stok Fisik</th>
                                    <th>Selisih</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold" id="detail_items">
                                @forelse($stockOpname->details as $detail)
                                    <tr id="detail-{{ $detail->id }}">
                                        <td>{{ $detail->obat->nama_obat }}</td>
                                        <td>{{ $detail->satuan->nama }}</td>
                                        <td>{{ $detail->no_batch }}</td>
                                        <td>{{ date('d/m/Y', strtotime($detail->tanggal_expired)) }}</td>
                                        <td>{{ $detail->stok_sistem }}</td>
                                        <td>{{ $detail->stok_fisik }}</td>
                                        <td>
                                            @if ($detail->selisih > 0)
                                                <span class="badge badge-light-success">+{{ $detail->selisih }}</span>
                                            @elseif($detail->selisih < 0)
                                                <span class="badge badge-light-danger">{{ $detail->selisih }}</span>
                                            @else
                                                <span class="badge badge-light-primary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-icon btn-light-danger btn-sm"
                                                onclick="removeObat('{{ $detail->id }}')">
                                                <i class="ki-duotone ki-trash fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-data-row">
                                        <td colspan="8" class="text-center py-4">Belum ada data obat</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            // Initialize Select2
            $('#lokasi_id, #obat_id, #satuan_id, #tindakan').select2({
                minimumResultsForSearch: 10
            });

            // Form Update Validation
            $("#formUpdate").validate({
                submitHandler: function(form) {
                    $('#btnSimpan').attr('data-kt-indicator', 'on').prop('disabled', true);
                    form.submit();
                }
            });

            // Complete Form Confirmation
            $('#btnComplete').click(function() {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Stock opname akan diselesaikan dan stok akan disesuaikan dengan data fisik",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, selesaikan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#btnComplete').attr('data-kt-indicator', 'on').prop('disabled', true);
                        $('#formComplete').submit();
                    }
                });
            });

            // Handle location selection
            $('#lokasi_id').on('change', function() {
                const lokasi_id = $(this).val();
                if (lokasi_id) {
                    $('#obat_id').prop('disabled', false).val('').trigger('change');
                    resetObatForm();
                } else {
                    $('#obat_id').prop('disabled', true).val('').trigger('change');
                    resetObatForm();
                }
            });

            // Setup obat select2 with AJAX
            $('#obat_id').select2({
                ajax: {
                    url: "{{ route('stock_opname.search_obat') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            lokasi_id: $('#lokasi_id').val()
                        };
                    },
                    processResults: function(data) {
                        if (data.error) {
                            toastr.error('Error: ' + data.error);
                            return {
                                results: []
                            };
                        }

                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.nama_obat + ' (' + item.kode_obat + ')',
                                    id: item.id,
                                    data: item
                                };
                            })
                        };
                    },
                    cache: false,
                    error: function(xhr, status, error) {
                        toastr.error('Error saat mencari obat: ' + error);
                        console.error('AJAX error:', status, error);
                    }
                },
                minimumInputLength: 2,
                language: {
                    inputTooShort: function() {
                        return "Masukkan minimal 2 karakter untuk mencari obat";
                    },
                    searching: function() {
                        return "Mencari obat...";
                    },
                    noResults: function() {
                        return "Tidak ada obat yang ditemukan";
                    }
                }
            }).on('select2:select', function(e) {
                const obat = e.params.data.data;
                resetObatForm();

                // Show detail container
                $('#detail_container').removeClass('d-none');

                // Show loading indicator
                $('#satuan_id').html('<option value="">Loading...</option>');

                // Get stock details
                $.ajax({
                    url: "{{ route('stock_opname.get_stok_detail') }}",
                    type: 'POST',
                    data: {
                        obat_id: obat.id,
                        lokasi_id: $('#lokasi_id').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Populate satuan dropdown
                        const satuanSelect = $('#satuan_id');
                        satuanSelect.empty();
                        satuanSelect.append('<option value=""></option>');

                        if (response.error) {
                            toastr.error('Error: ' + response.error);
                            return;
                        }

                        if (response.length > 0) {
                            $.each(response, function(i, stok) {
                                satuanSelect.append('<option value="' + stok.satuan.id +
                                    '" data-stok="' + stok.jumlah +
                                    '" data-batch="' + stok.no_batch +
                                    '" data-expired="' + stok.tanggal_expired +
                                    '">' + stok.satuan.nama + '</option>');
                            });
                            toastr.success('Data stok berhasil dimuat');
                        } else {
                            toastr.warning(
                                'Tidak ada stok untuk obat ini di lokasi yang dipilih');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Error saat memuat data stok: ' + error);
                        console.error('AJAX error:', status, error, xhr.responseText);
                    }
                });
            });

            // Handle satuan selection
            $('#satuan_id').on('change', function() {
                const selected = $(this).find(':selected');
                if (selected.val()) {
                    const stok = selected.data('stok');
                    const batch = selected.data('batch');
                    const expired = selected.data('expired');

                    // Format the date correctly (from yyyy-mm-dd hh:mm:ss to yyyy-mm-dd)
                    const formattedDate = expired ? expired.split(' ')[0] : '';

                    $('#no_batch').val(batch);
                    $('#tanggal_expired').val(formattedDate);
                    $('#stok_sistem').val(stok);
                    $('#stok_fisik').val(stok);
                    $('#selisih').val(0);

                    // Show stock containers
                    $('#stok_container, #tindakan_container').removeClass('d-none');
                } else {
                    $('#stok_container, #tindakan_container').addClass('d-none');
                    $('#no_batch, #tanggal_expired, #stok_sistem, #stok_fisik, #selisih').val('');
                }
            });

            // Calculate difference between system and physical stock
            $('#stok_fisik').on('input', function() {
                const sistem = parseInt($('#stok_sistem').val()) || 0;
                const fisik = parseInt($(this).val()) || 0;
                const selisih = fisik - sistem;
                $('#selisih').val(selisih);

                // Suggest tindakan based on selisih
                if (selisih < 0) {
                    $('#tindakan').val('Investigasi lebih lanjut').trigger('change');
                } else if (selisih > 0) {
                    $('#tindakan').val('Penyesuaian stok').trigger('change');
                } else {
                    $('#tindakan').val('').trigger('change');
                }
            });

            // Add medicine to stock opname
            $('#btn_tambah_obat').on('click', function() {
                const button = $(this);
                button.attr('data-kt-indicator', 'on');

                // Reset validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                // Validate form
                let isValid = true;
                let errors = {};

                if (!$('#lokasi_id').val()) {
                    isValid = false;
                    errors.lokasi = 'Lokasi harus dipilih';
                    $('#lokasi_id').addClass('is-invalid');
                    $('.lokasi-error').text('Lokasi harus dipilih');
                }

                if (!$('#obat_id').val()) {
                    isValid = false;
                    errors.obat = 'Obat harus dipilih';
                    $('#obat_id').addClass('is-invalid');
                    $('.obat-error').text('Obat harus dipilih');
                }

                if (!$('#satuan_id').val()) {
                    isValid = false;
                    errors.satuan = 'Satuan harus dipilih';
                    $('#satuan_id').addClass('is-invalid');
                    $('.satuan-error').text('Satuan harus dipilih');
                }

                if (!$('#no_batch').val()) {
                    isValid = false;
                    errors.batch = 'No. Batch harus diisi';
                    $('#no_batch').addClass('is-invalid');
                    $('.batch-error').text('No. Batch harus diisi');
                }

                if (!$('#tanggal_expired').val()) {
                    isValid = false;
                    errors.expired = 'Tanggal expired harus diisi';
                    $('#tanggal_expired').addClass('is-invalid');
                    $('.expired-error').text('Tanggal expired harus diisi');
                }

                if (!$('#stok_sistem').val()) {
                    isValid = false;
                    errors.sistem = 'Stok sistem harus diisi';
                    $('#stok_sistem').addClass('is-invalid');
                    $('.sistem-error').text('Stok sistem harus diisi');
                }

                if (!$('#stok_fisik').val()) {
                    isValid = false;
                    errors.fisik = 'Stok fisik harus diisi';
                    $('#stok_fisik').addClass('is-invalid');
                    $('.fisik-error').text('Stok fisik harus diisi');
                }

                if (!isValid) {
                    button.removeAttr('data-kt-indicator');
                    return;
                }

                // Prepare data
                const data = {
                    obat_id: $('#obat_id').val(),
                    satuan_id: $('#satuan_id').val(),
                    lokasi_id: $('#lokasi_id').val(),
                    no_batch: $('#no_batch').val(),
                    tanggal_expired: $('#tanggal_expired').val(),
                    stok_sistem: $('#stok_sistem').val(),
                    stok_fisik: $('#stok_fisik').val(),
                    tindakan: $('#tindakan').val() || '',
                    catatan: $('#catatan').val() || '',
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                console.log('Sending data:', data);

                // Send request
                $.ajax({
                    url: "{{ route('stock_opname.add_obat', $stockOpname) }}",
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        console.log('Success response:', response);

                        if (response.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                // Reload the page to show updated data
                                location.reload();
                            });
                        } else {
                            toastr.error(response.message);
                            button.removeAttr('data-kt-indicator');
                        }
                    },
                    error: function(xhr, status, error) {
                        button.removeAttr('data-kt-indicator');
                        console.error('Error response:', xhr.responseText);

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                // Display validation errors
                                let errorMessages = [];
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    errorMessages.push(value);
                                    // Highlight the field with error
                                    $('#' + key).addClass('is-invalid');
                                    $('.' + key + '-error').text(value);
                                });

                                toastr.error(errorMessages.join('<br>'));
                            } else if (xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
                            }
                        } else {
                            toastr.error('Terjadi kesalahan saat menambahkan obat: ' + error);
                        }
                    }
                });
            });

            // Initialize DataTable
            $('#details_table').DataTable({
                "paging": true,
                "pageLength": 10,
                "searching": true,
                "info": true,
                "language": {
                    "lengthMenu": "Tampilkan _MENU_",
                    "zeroRecords": "Tidak ada data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(disaring dari _MAX_ data)",
                    "search": "Cari:",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });
        });

        // Reset form after obat selection changes
        function resetObatForm() {
            $('#detail_container, #stok_container, #tindakan_container').addClass('d-none');
            $('#satuan_id').empty().append('<option value=""></option>');
            $('#no_batch, #tanggal_expired, #stok_sistem, #stok_fisik, #selisih, #catatan').val('');
            $('#tindakan').val('').trigger('change');
        }

        // Remove medicine from stock opname
        function removeObat(detailId) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Obat ini akan dihapus dari stock opname",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('stock_opname.remove_obat', ['stockOpname' => $stockOpname->id, 'detail' => '']) }}" +
                            detailId,
                        method: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove row from table
                                $('#detail-' + detailId).fadeOut(300, function() {
                                    $(this).remove();

                                    // If no more items, show no-data message
                                    if ($('#detail_items tr').length === 0) {
                                        $('#detail_items').html(
                                            '<tr id="no-data-row"><td colspan="8" class="text-center py-4">Belum ada data obat</td></tr>'
                                        );
                                    }
                                });

                                toastr.success(response.message);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error response:', xhr.responseText);

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
                            } else {
                                toastr.error('Terjadi kesalahan saat menghapus obat: ' + error);
                            }
                        }
                    });
                }
            });
        }
    </script>
@endpush
