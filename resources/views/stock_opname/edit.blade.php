@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Edit Stock Opname</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">{{ $stockOpname->kode }}</span>
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.index') }}" class="btn btn-secondary me-2">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <path d="M9.60001 11H21C21.6 11 22 11.4 22 12C22 12.6 21.6 13 21 13H9.60001V11Z"
                                fill="currentColor" />
                            <path opacity="0.3" d="M9.6 20V4L2.3 11.3C1.9 11.7 1.9 12.3 2.3 12.7L9.6 20Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    Kembali
                </a>
                <form method="POST" action="{{ route('stock_opname.complete', $stockOpname) }}" class="d-inline"
                    onsubmit="return confirm('Yakin ingin menyelesaikan stock opname? Stok akan diperbarui sesuai data fisik.')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <path
                                    d="M9.89557 13.4982L7.79487 11.2651C7.26967 10.7068 6.38251 10.7068 5.85731 11.2651C5.37559 11.7772 5.37559 12.5757 5.85731 13.0878L9.74989 17.2257C10.1448 17.6455 10.8118 17.6455 11.2066 17.2257L18.1427 9.85252C18.6244 9.34044 18.6244 8.54191 18.1427 8.02984C17.6175 7.47154 16.7303 7.47154 16.2051 8.02984L11.061 13.4982C10.7451 13.834 10.2115 13.834 9.89557 13.4982Z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                        Selesaikan
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body pt-0">
            <form action="{{ route('stock_opname.update', $stockOpname) }}" method="POST" id="update_form">
                @csrf
                @method('PUT')
                <div class="row mb-6">
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="required form-label">Kode Stock Opname</label>
                            <input type="text" class="form-control" value="{{ $stockOpname->kode }}" readonly />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="required form-label">Tanggal</label>
                            <input type="date" class="form-control" value="{{ $stockOpname->tanggal->format('Y-m-d') }}"
                                readonly />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($stockOpname->status) }}"
                                readonly />
                            <input type="hidden" name="status" value="{{ $stockOpname->status }}" />
                        </div>
                    </div>
                </div>
                <div class="row mb-6">
                    <div class="col-md-12">
                        <div class="mb-5">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                placeholder="Keterangan stock opname">{{ old('keterangan', $stockOpname->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </form>

            <div class="separator separator-dashed my-5"></div>

            <div class="mb-5">
                <h3 class="fw-bold fs-4 mb-4">Tambah Obat</h3>

                <form id="add_obat_form" class="mb-8" onsubmit="return false;">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="required form-label">Lokasi</label>
                                <select id="lokasi_id" class="form-select" data-control="select2"
                                    data-placeholder="Pilih Lokasi">
                                    <option value=""></option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback lokasi-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="required form-label">Obat</label>
                                <select id="obat_id" class="form-select" data-control="select2"
                                    data-placeholder="Pilih Obat" disabled>
                                    <option value=""></option>
                                </select>
                                <div class="invalid-feedback obat-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5" id="detail_container" style="display: none;">
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="required form-label">Satuan</label>
                                <select id="satuan_id" class="form-select" data-placeholder="Pilih Satuan">
                                    <option value=""></option>
                                </select>
                                <div class="invalid-feedback satuan-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="required form-label">No. Batch</label>
                                <input type="text" id="no_batch" class="form-control" placeholder="Nomor batch" />
                                <div class="invalid-feedback batch-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="required form-label">Tanggal Expired</label>
                                <input type="date" id="tanggal_expired" class="form-control" />
                                <div class="invalid-feedback expired-error"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5" id="stok_container" style="display: none;">
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="required form-label">Stok Sistem</label>
                                <input type="number" id="stok_sistem" class="form-control" min="0" readonly />
                                <div class="invalid-feedback sistem-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="required form-label">Stok Fisik</label>
                                <input type="number" id="stok_fisik" class="form-control" min="0" />
                                <div class="invalid-feedback fisik-error"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="form-label">Selisih</label>
                                <input type="number" id="selisih" class="form-control" readonly />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-5" id="tindakan_container" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-5">
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
                            <div class="mb-5">
                                <label class="form-label">Catatan</label>
                                <textarea id="catatan" class="form-control" rows="3" placeholder="Catatan tambahan"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" id="btn_tambah_obat">
                            <span class="indicator-label">Tambah Obat</span>
                            <span class="indicator-progress">Menambahkan...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="separator separator-dashed my-5"></div>

            <div class="mb-5">
                <h3 class="fw-bold fs-4 mb-4">Daftar Obat</h3>
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
                                            <span class="text-success">+{{ $detail->selisih }}</span>
                                        @elseif($detail->selisih < 0)
                                            <span class="text-danger">{{ $detail->selisih }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                            onclick="removeObat('{{ $detail->id }}')">
                                            <span class="svg-icon svg-icon-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                        fill="currentColor"></path>
                                                    <path opacity="0.5"
                                                        d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                        fill="currentColor"></path>
                                                    <path opacity="0.5"
                                                        d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                            </span>
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

            <div class="separator separator-dashed my-5"></div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-light" onclick="window.history.back()">Batal</button>
                <button type="button" class="btn btn-primary"
                    onclick="document.getElementById('update_form').submit();">Simpan Perubahan</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
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
                            q: params.term,
                            lokasi_id: $('#lokasi_id').val()
                        };
                    },
                    processResults: function(data) {
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
                    cache: true
                },
                minimumInputLength: 2
            }).on('select2:select', function(e) {
                const obat = e.params.data.data;
                resetObatForm();

                // Show detail container
                $('#detail_container').show();

                // Get stock details
                $.ajax({
                    url: "{{ route('stock_opname.get_stok_detail') }}",
                    data: {
                        obat_id: obat.id,
                        lokasi_id: $('#lokasi_id').val()
                    },
                    success: function(response) {
                        // Populate satuan dropdown
                        const satuanSelect = $('#satuan_id');
                        satuanSelect.empty();
                        satuanSelect.append('<option value=""></option>');

                        if (response.length > 0) {
                            $.each(response, function(i, stok) {
                                // Now the tanggal_expired should already be in YYYY-MM-DD format from the server
                                satuanSelect.append('<option value="' + stok.satuan.id +
                                    '" data-stok="' + stok.jumlah +
                                    '" data-batch="' + stok.no_batch +
                                    '" data-expired="' + stok.tanggal_expired +
                                    '">' + stok.satuan.nama + '</option>');
                            });
                        }
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
                    $('#stok_container, #tindakan_container').show();
                } else {
                    $('#stok_container, #tindakan_container').hide();
                    $('#no_batch, #tanggal_expired, #stok_sistem, #stok_fisik, #selisih').val('');
                }
            });

            // Calculate difference between system and physical stock
            $('#stok_fisik').on('input', function() {
                const sistem = parseInt($('#stok_sistem').val()) || 0;
                const fisik = parseInt($(this).val()) || 0;
                const selisih = fisik - sistem;
                $('#selisih').val(selisih);
            });

            // Add medicine to stock opname
            $('#btn_tambah_obat').on('click', function() {
                const button = $(this);
                button.attr('data-kt-indicator', 'on');

                // Reset validation errors
                $('.is-invalid').removeClass('is-invalid');

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
                    tindakan: $('#tindakan').val(),
                    catatan: $('#catatan').val(),
                    _token: '{{ csrf_token() }}'
                };

                console.log('Sending data to server:', data);

                // Send request
                $.ajax({
                    url: "{{ route('stock_opname.add_obat', $stockOpname) }}",
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.success) {
                            // Show success message
                            toastr.success(response.message);

                            // Reload the page to show updated data
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                        button.removeAttr('data-kt-indicator');
                    },
                    error: function(xhr, status, error) {
                        console.log('Error:', status, error);
                        console.log('Response:', xhr.responseText);
                        button.removeAttr('data-kt-indicator');

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Terjadi kesalahan saat menambahkan obat: ' + error);
                        }
                    }
                });
            });

            // Initialize DataTable
            $('#details_table').DataTable({
                "paging": false,
                "searching": false,
                "info": false
            });
        });

        // Reset form after obat selection changes
        function resetObatForm() {
            $('#detail_container, #stok_container, #tindakan_container').hide();
            $('#satuan_id').empty().append('<option value=""></option>');
            $('#no_batch, #tanggal_expired, #stok_sistem, #stok_fisik, #selisih, #catatan').val('');
            $('#tindakan').val('').trigger('change');
        }

        // Remove medicine from stock opname
        function removeObat(detailId) {
            if (confirm('Apakah Anda yakin ingin menghapus obat ini dari stock opname?')) {
                $.ajax({
                    url: "{{ route('stock_opname.index') }}/" + {{ $stockOpname->id }} + "/remove-obat/" +
                        detailId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove row from table
                            $('#detail-' + detailId).remove();
                            toastr.success(response.message);

                            // If no more items, show no-data message
                            if ($('#detail_items tr').length === 0) {
                                $('#detail_items').html(
                                    '<tr id="no-data-row"><td colspan="8" class="text-center py-4">Belum ada data obat</td></tr>'
                                );
                            }
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Terjadi kesalahan saat menghapus obat');
                        }
                    }
                });
            }
        }
    </script>
@endsection
