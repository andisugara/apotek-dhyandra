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
                                        data-placeholder="Pilih Obat">
                                        <option value=""></option>
                                    </select>
                                    <div class="fv-plugins-message-container invalid-feedback obat-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-5 d-none" id="detail_container">
                            <div class="col-md-12">
                                <div class="form-group mb-5">
                                    <label class="form-label fw-bold">Detail Obat</label>
                                    <div class="table-responsive">
                                        <table
                                            class="table table-rounded table-striped border border-gray-300 gs-0 gy-3 gx-5"
                                            id="satuan_table">
                                            <thead>
                                                <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-300">
                                                    <th>Satuan</th>
                                                    <th>Stok Sistem</th>
                                                    <th>Stok Fisik</th>
                                                    <th>Selisih</th>
                                                    <th>Keterangan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="satuan_items">
                                                <!-- Data will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inputs for adding item -->
                        <div class="d-none">
                            <input type="hidden" id="stok_sistem" value="0" />
                            <input type="hidden" id="stok_fisik" value="0" />
                            <input type="hidden" id="selisih" value="0" />
                            <input type="hidden" id="satuan_id" value="" />
                            <input type="hidden" id="keterangan" value="" />
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
                                    <th>Lokasi</th>
                                    <th>Stok Sistem</th>
                                    <th>Stok Fisik</th>
                                    <th>Selisih</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold" id="detail_items">
                                @forelse($stockOpname->details as $detail)
                                    <tr id="detail-{{ $detail->id }}">
                                        <td>{{ $detail->obat->nama_obat }}</td>
                                        <td>{{ $detail->satuan->nama }}</td>
                                        <td>{{ $detail->lokasi->nama }}</td>
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
                                        <td>{{ $detail->keterangan ?: '-' }}</td>
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

        // Reset form after obat selection changes
        function resetObatForm() {
            $('#detail_container').addClass('d-none');
            $('#satuan_items').empty();
            $('#stok_sistem, #stok_fisik, #selisih, #keterangan').val('');
        }

        // Check if a satuan is already added to stock opname
        function checkIfSatuanAlreadyAdded(obatId, satuanId) {
            let isAdded = false;
            $('#details_table tbody tr').each(function() {
                if ($(this).attr('id') && $(this).attr('id').startsWith('detail-')) {
                    const detailRow = $(this);
                    const obatName = detailRow.find('td:eq(0)').text().trim();
                    const satuanName = detailRow.find('td:eq(1)').text().trim();
                    const selectedObatName = $('#obat_id option:selected').text().trim();
                    const selectedSatuanName = $(`#satuan_items tr[data-satuan-id="${satuanId}"] td:first`).text()
                        .trim();

                    if (obatName === selectedObatName && satuanName === selectedSatuanName) {
                        isAdded = true;
                        return false; // Break the loop
                    }
                }
            });
            return isAdded;
        }

        // Function to load satuan table for selected medicine
        function loadSatuanTable(obatId, lokasiId) {
            // Reset previous data
            resetObatForm();

            // Show loading indicator
            $('#satuan_items').html(
                '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>'
                );
            $('#detail_container').removeClass('d-none');

            // Get stock details
            $.ajax({
                url: "{{ route('stock_opname.get_stok_detail') }}",
                type: 'POST',
                data: {
                    obat_id: obatId,
                    lokasi_id: lokasiId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.error) {
                        toastr.error('Error: ' + response.error);
                        $('#satuan_items').html('<tr><td colspan="6" class="text-center py-4">Error: ' +
                            response.error + '</td></tr>');
                        return;
                    }

                    if (response.length > 0) {
                        // Clear and show satuan table
                        $('#satuan_items').empty();
                        $('#detail_container').removeClass('d-none');

                        // Add rows for each satuan
                        $.each(response, function(i, item) {
                            const rowId = 'row-' + item.satuan_id;

                            // Check if this satuan is already added to stock opname
                            const isAlreadyAdded = checkIfSatuanAlreadyAdded(item.obat_id, item
                                .satuan_id);

                            // Format stok sistem with decimal precision
                            const stokSistemFormatted = parseFloat(item.stok_sistem).toFixed(2);

                            // Create the row with form inputs for stock
                            const row = `
                                <tr id="${rowId}" data-satuan-id="${item.satuan_id}" data-stok-sistem="${item.stok_sistem}"
                                    class="${isAlreadyAdded ? 'bg-light-success' : ''}">
                                    <td>${item.satuan_nama}</td>
                                    <td>${stokSistemFormatted}</td>
                                    <td>
                                        <input type="number" class="form-control stok-fisik-input"
                                            min="0" value="${stokSistemFormatted}" step="0.01"
                                            data-satuan-id="${item.satuan_id}"
                                            data-stok-sistem="${item.stok_sistem}"
                                            ${isAlreadyAdded ? 'disabled' : ''}>
                                    </td>
                                    <td class="selisih-cell">0</td>
                                    <td>
                                        <input type="text" class="form-control keterangan-input"
                                            placeholder="Keterangan (opsional)"
                                            data-satuan-id="${item.satuan_id}"
                                            ${isAlreadyAdded ? 'disabled' : ''}>
                                    </td>
                                    <td>
                                        ${isAlreadyAdded ?
                                        `<button type="button" class="btn btn-sm btn-success" disabled>
                                                <i class="ki-duotone ki-check fs-2"></i> Ditambahkan
                                            </button>` :
                                        `<button type="button" class="btn btn-sm btn-primary btn-add-item"
                                                data-satuan-id="${item.satuan_id}"
                                                data-satuan-nama="${item.satuan_nama}"
                                                data-obat-id="${item.obat_id}"
                                                data-obat-nama="${item.obat_nama}"
                                                data-lokasi-id="${item.lokasi_id}">
                                                <i class="ki-duotone ki-plus-circle fs-2"></i>
                                                Tambahkan
                                            </button>`}
                                    </td>
                                </tr>
                            `;

                            // Append the row to the table
                            $('#satuan_items').append(row);
                        });

                        // Add event listener for stok fisik input to calculate selisih
                        $('.stok-fisik-input').on('input', function() {
                            const stokSistem = parseFloat($(this).data('stok-sistem')) || 0;
                            const stokFisik = parseFloat($(this).val()) || 0;
                            const selisih = stokFisik - stokSistem;

                            // Format selisih with colors
                            let selisihHtml;
                            if (selisih > 0) {
                                selisihHtml =
                                    `<span class="badge badge-light-success">+${selisih.toFixed(2)}</span>`;
                            } else if (selisih < 0) {
                                selisihHtml =
                                    `<span class="badge badge-light-danger">${selisih.toFixed(2)}</span>`;
                            } else {
                                selisihHtml = `<span class="badge badge-light-primary">0</span>`;
                            }

                            // Update selisih cell
                            $(this).closest('tr').find('.selisih-cell').html(selisihHtml);
                        });

                        // Add event listener for add item button
                        $('.btn-add-item').on('click', function() {
                            const satuanId = $(this).data('satuan-id');
                            const obatId = $(this).data('obat-id');
                            const obatNama = $(this).data('obat-nama');
                            const satuanNama = $(this).data('satuan-nama');
                            const lokasiId = $(this).data('lokasi-id');
                            const row = $(`#row-${satuanId}`);

                            const stokSistem = parseFloat(row.data('stok-sistem')) || 0;
                            const stokFisik = parseFloat(row.find('.stok-fisik-input').val()) || 0;
                            const keterangan = row.find('.keterangan-input').val();

                            // Add item to stock opname
                            addObatToStockOpname(
                                obatId,
                                satuanId,
                                lokasiId,
                                stokSistem,
                                stokFisik,
                                keterangan,
                                obatNama,
                                satuanNama
                            );
                        });

                        toastr.success('Data stok berhasil dimuat');
                    } else {
                        $('#satuan_items').html(
                            '<tr><td colspan="6" class="text-center py-4">Tidak ada satuan untuk obat ini</td></tr>'
                            );
                        $('#detail_container').removeClass('d-none');
                        toastr.warning('Tidak ada satuan terkait untuk obat ini');
                    }
                },
                error: function(xhr, status, error) {
                    $('#satuan_items').html('<tr><td colspan="6" class="text-center py-4">Error: ' + error +
                        '</td></tr>');
                    toastr.error('Error saat memuat data stok: ' + error);
                    console.error('AJAX error:', status, error, xhr.responseText);
                }
            });
        }

        // Function to add obat to stock opname
        function addObatToStockOpname(obatId, satuanId, lokasiId, stokSistem, stokFisik, keterangan, obatNama, satuanNama) {
            // Prepare data
            const data = {
                obat_id: obatId,
                satuan_id: satuanId,
                lokasi_id: lokasiId,
                stok_sistem: stokSistem,
                stok_fisik: stokFisik,
                keterangan: keterangan,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Add loading indicator to button
            const button = $(`button[data-satuan-id="${satuanId}"]`);
            button.html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambahkan...'
                );
            button.prop('disabled', true);

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
                        toastr.success(`Berhasil menambahkan ${obatNama} (${satuanNama}) ke stock opname`);

                        // Disable the row after adding
                        $(`#row-${satuanId}`).addClass('bg-light-success');
                        $(`#row-${satuanId} input`).prop('disabled', true);
                        button.html('<i class="ki-duotone ki-check fs-2"></i> Ditambahkan');

                        // Reload the page to show updated data
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                        button.html('<i class="ki-duotone ki-plus-circle fs-2"></i> Tambahkan');
                        button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    button.html('<i class="ki-duotone ki-plus-circle fs-2"></i> Tambahkan');
                    button.prop('disabled', false);
                    console.error('Error response:', xhr.responseText);

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            // Display validation errors
                            let errorMessages = [];
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessages.push(value);
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
                        url: "{{ route('stock_opname.remove_obat', ['stockOpname' => $stockOpname->id, 'detail' => ':detailId']) }}"
                            .replace(':detailId', detailId),
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

        $(document).ready(function() {
            // Initialize Select2 for location
            $('#lokasi_id').select2({
                minimumResultsForSearch: 10,
                placeholder: 'Pilih Lokasi'
            });

            // Initialize Select2 for obat with AJAX
            $('#obat_id').select2({
                placeholder: 'Pilih Obat',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('stock_opname.search_obat') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            lokasi_id: $('#lokasi_id').val() || ''
                        };
                    },
                    processResults: function(data) {
                        console.log('Received data:', data);

                        if (!data || data.error) {
                            if (data && data.error) {
                                toastr.error('Error: ' + data.error);
                            }
                            return {
                                results: []
                            };
                        }

                        return {
                            results: $.map(data, function(item) {
                                if (!item || !item.nama_obat) return null;
                                return {
                                    text: item.nama_obat + ' (' + (item.kode_obat || '') + ')',
                                    id: item.id,
                                    data: item
                                };
                            }).filter(function(item) {
                                return item !== null;
                            })
                        };
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr, status, error);
                        toastr.error('Error saat mencari obat: ' + error);
                    }
                },
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
            });

            // Handle obat selection
            $('#obat_id').on('select2:select', function(e) {
                const obatId = $(this).val();
                const lokasiId = $('#lokasi_id').val();

                if (obatId && lokasiId) {
                    loadSatuanTable(obatId, lokasiId);
                } else {
                    toastr.warning('Pilih lokasi dan obat terlebih dahulu');
                }
            });

            // Handle location selection
            $('#lokasi_id').on('change', function() {
                // Reset obat selection
                $('#obat_id').val(null).trigger('change');
                resetObatForm();
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

            // Add medicine to stock opname button
            $('#btn_tambah_obat').on('click', function() {
                // This is now just a placeholder button
                // All adding functionality is now handled by the individual row buttons
                toastr.info(
                    'Silahkan pilih obat terlebih dahulu, kemudian klik tombol Tambahkan pada satuan yang ingin ditambahkan'
                    );
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
    </script>
@endpush
