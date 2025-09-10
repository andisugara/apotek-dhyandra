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
                            <!-- Hidden input for location -->
                            <input type="hidden" id="lokasi_id" value="{{ $locations->first()->id }}">
                            <div class="col-md-12">
                                <div class="form-group mb-5">
                                    <label class="required form-label">Obat</label>
                                    <select id="obat_id" class="form-select" data-control="select2"
                                        data-placeholder="Cari Obat">
                                        <option value=""></option>
                                    </select>
                                    <div class="fv-plugins-message-container invalid-feedback obat-error"></div>
                                    <small class="form-text text-muted mt-2">
                                        Cari dan pilih obat untuk langsung menambahkannya ke daftar stock opname
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Medicine units container - will be populated with units table -->
                        <div id="detail_container" class="d-none"></div>

                        <div class="alert alert-info d-flex align-items-center p-3 mb-3">
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-info">Cara Penggunaan</h4>
                                <span>1. Cari dan pilih obat dari dropdown di atas</span>
                                <span>2. Semua satuan obat akan ditampilkan untuk pengecekan stok</span>
                                <span>3. Isi stok fisik untuk setiap satuan dan tambahkan ke daftar</span>
                                <span>4. Anda dapat menggunakan tombol "Tambahkan Semua Satuan" untuk menambahkan
                                    sekaligus</span>
                                <span>5. Setelah selesai menambahkan semua obat, klik tombol "Selesaikan" di bagian
                                    atas</span>
                            </div>
                        </div>

                        <!-- Hidden inputs for compatibility -->
                        <div class="d-none">
                            <input type="hidden" id="stok_sistem" value="0" />
                            <input type="hidden" id="stok_fisik" value="0" />
                            <input type="hidden" id="selisih" value="0" />
                            <input type="hidden" id="satuan_id" value="" />
                            <input type="hidden" id="keterangan" value="" />
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary btn-sm me-3" onclick="resetForm()"
                                id="btn_tambah_obat">
                                <i class="ki-duotone ki-plus fs-2"></i> Tambahkan Obat
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
        // Function to save all satuan items at once
        function saveAllSatuanItems() {
            // Check if there are items to add
            if ($('#satuan_items tr').length === 0) {
                toastr.warning('Silahkan pilih obat terlebih dahulu');
                return;
            }

            // Get only valid rows (not disabled or already added)
            const rows = $('#satuan_items tr:not(.bg-light-success)');

            if (rows.length === 0) {
                toastr.warning('Tidak ada satuan yang dapat ditambahkan');
                return;
            }

            // Disable the button and show loading indicator
            $('#add_all_units').attr('data-kt-indicator', 'on').prop('disabled', true);

            let successCount = 0;
            let errorCount = 0;
            const totalCount = rows.length;

            // Process rows one by one to avoid race conditions
            function processRow(index) {
                if (index >= rows.length) {
                    // All rows processed, show summary
                    setTimeout(function() {
                        $('#add_all_units').removeAttr('data-kt-indicator').prop('disabled', false);

                        if (successCount > 0 && errorCount === 0) {
                            toastr.success(
                                `Berhasil menambahkan ${successCount} satuan ke stock opname`);
                            // Reload the page to show updated data
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else if (successCount > 0 && errorCount > 0) {
                            toastr.warning(
                                `Berhasil: ${successCount}, Gagal: ${errorCount}. Beberapa satuan tidak dapat ditambahkan`
                            );
                        } else if (errorCount > 0) {
                            toastr.error(`Gagal menambahkan ${errorCount} satuan`);
                        }
                    }, 500);
                    return;
                }

                const row = $(rows[index]);
                const satuanId = row.data('satuan-id');
                const stokSistem = parseFloat(row.data('stok-sistem')) || 0;

                // IMPORTANT: Get the current values directly from the inputs right before sending
                // This ensures we capture any changes made by the user
                const inputElement = row.find('.stok-fisik-input');
                const stokFisik = parseFloat(inputElement.val()) || 0;
                const keterangan = row.find('.keterangan-input').val() || '';

                const obatId = $('#obat_id').val();
                const lokasiId = $('#lokasi_id').val();
                const obatNama = $('#obat_id option:selected').text();
                const satuanNama = row.find('td:first').text().trim();

                // Skip if already added
                if (row.hasClass('bg-light-success')) {
                    processRow(index + 1);
                    return;
                }

                // Add loading state to the row
                row.addClass('bg-light-warning');

                // Debug log to check values before sending
                console.log(
                    `Adding ${satuanNama} with stok_fisik: ${stokFisik} (input value: ${inputElement.val()}), stok_sistem: ${stokSistem}`
                );

                // Prepare data with explicit token
                const data = {
                    obat_id: obatId,
                    satuan_id: satuanId,
                    lokasi_id: lokasiId,
                    stok_sistem: stokSistem,
                    stok_fisik: stokFisik,
                    keterangan: keterangan,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                // Send request
                $.ajax({
                    url: "{{ route('stock_opname.add_obat', $stockOpname) }}",
                    method: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            successCount++;
                            // Mark row as added
                            row.removeClass('bg-light-warning').addClass('bg-light-success');
                            row.find('input').prop('disabled', true);
                        } else {
                            errorCount++;
                            row.removeClass('bg-light-warning').addClass('bg-light-danger');
                            console.error('Error adding item:', response.message);
                            toastr.error(response.message || 'Gagal menambahkan obat');
                        }
                    },
                    error: function(xhr) {
                        errorCount++;
                        row.removeClass('bg-light-warning').addClass('bg-light-danger');
                        console.error('AJAX error:', xhr.responseText);

                        let errorMessage = 'Gagal menambahkan obat';
                        if (xhr.status === 419) {
                            errorMessage = 'CSRF token mismatch. Coba refresh halaman dan coba lagi.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // Process the next row
                        processRow(index + 1);
                    }
                });
            }

            // Start processing rows from index 0
            processRow(0);
        }

        // Function to reset the form when Add Medicine button is clicked
        function resetForm() {
            console.log('Resetting form');
            $('#obat_id').val(null).trigger('change');
            $('#detail_container').addClass('d-none').html('');
        }

        // Function to save all satuan items at once
        function saveAllSatuanItems() {
            // Check if there are items to add
            if ($('#satuan_items tr').length === 0) {
                toastr.warning('Silahkan pilih obat terlebih dahulu');
                return;
            }

            // Get only valid rows (not disabled or already added)
            const rows = $('#satuan_items tr:not(.bg-light-success)');

            if (rows.length === 0) {
                toastr.warning('Tidak ada satuan yang dapat ditambahkan');
                return;
            }

            // Disable the button and show loading indicator
            $('#add_all_units').attr('data-kt-indicator', 'on').prop('disabled', true);

            let successCount = 0;
            let errorCount = 0;
            const totalCount = rows.length;

            // Process rows one by one to avoid race conditions
            function processRow(index) {
                if (index >= rows.length) {
                    // All rows processed, show summary
                    setTimeout(function() {
                        $('#add_all_units').removeAttr('data-kt-indicator').prop('disabled', false);

                        if (successCount > 0 && errorCount === 0) {
                            toastr.success(
                                `Berhasil menambahkan ${successCount} satuan ke stock opname`);
                            // Reload the page to show updated data
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else if (successCount > 0 && errorCount > 0) {
                            toastr.warning(
                                `Berhasil: ${successCount}, Gagal: ${errorCount}. Beberapa satuan tidak dapat ditambahkan`
                            );
                        } else if (errorCount > 0) {
                            toastr.error(`Gagal menambahkan ${errorCount} satuan`);
                        }
                    }, 500);
                    return;
                }

                const row = $(rows[index]);
                const satuanId = row.data('satuan-id');
                const stokSistem = parseFloat(row.data('stok-sistem')) || 0;

                // IMPORTANT: Get the current values directly from the inputs right before sending
                // This ensures we capture any changes made by the user
                const inputElement = row.find('.stok-fisik-input');
                const stokFisik = parseFloat(inputElement.val()) || 0;
                const keterangan = row.find('.keterangan-input').val() || '';

                const obatId = $('#obat_id').val();
                const lokasiId = $('#lokasi_id').val();
                const obatNama = $('#obat_id option:selected').text();
                const satuanNama = row.find('td:first').text().trim();

                // Skip if already added
                if (row.hasClass('bg-light-success')) {
                    processRow(index + 1);
                    return;
                }

                // Add loading state to the row
                row.addClass('bg-light-warning');

                // Debug log to check values before sending
                console.log(
                    `Adding ${satuanNama} with stok_fisik: ${stokFisik} (input value: ${inputElement.val()}), stok_sistem: ${stokSistem}`
                );

                // Prepare data with explicit token
                const data = {
                    obat_id: obatId,
                    satuan_id: satuanId,
                    lokasi_id: lokasiId,
                    stok_sistem: stokSistem,
                    stok_fisik: stokFisik,
                    keterangan: keterangan,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                // Send request
                $.ajax({
                    url: "{{ route('stock_opname.add_obat', $stockOpname) }}",
                    method: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            successCount++;
                            // Mark row as added
                            row.removeClass('bg-light-warning').addClass('bg-light-success');
                            row.find('input').prop('disabled', true);
                        } else {
                            errorCount++;
                            row.removeClass('bg-light-warning').addClass('bg-light-danger');
                            console.error('Error adding item:', response.message);
                            toastr.error(response.message || 'Gagal menambahkan obat');
                        }
                    },
                    error: function(xhr) {
                        errorCount++;
                        row.removeClass('bg-light-warning').addClass('bg-light-danger');
                        console.error('AJAX error:', xhr.responseText);

                        let errorMessage = 'Gagal menambahkan obat';
                        if (xhr.status === 419) {
                            errorMessage = 'CSRF token mismatch. Coba refresh halaman dan coba lagi.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    },
                    complete: function() {
                        // Process the next row
                        processRow(index + 1);
                    }
                });
            }

            // Start processing rows from index 0
            processRow(0);
        }

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
                    const selectedSatuanName = $(`#satuan_items tr[data-satuan-id="${satuanId}"] td:first`)
                        .text()
                        .trim();

                    if (obatName === selectedObatName && satuanName === selectedSatuanName) {
                        isAdded = true;
                        return false; // Break the loop
                    }
                }
            });
            return isAdded;
        }

        // Function to get all units for a selected medicine and show them in a table
        function addSelectedMedicineToForm(obatId, obatName, lokasiId) {
            if (!obatId || !obatName) {
                toastr.error('Mohon pilih obat terlebih dahulu');
                return;
            }

            // Show loading state
            $('#detail_container').removeClass('d-none').html(`
                <div class="d-flex justify-content-center my-10">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Get stock details for all units of this medicine
            $.ajax({
                url: "{{ route('stock_opname.get_stok_detail') }}",
                method: 'GET',
                data: {
                    obat_id: obatId,
                    lokasi_id: lokasiId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Stock details response:', response);

                    // Check if we got any units
                    if (Array.isArray(response) && response.length > 0) {
                        // Create a table to display all units
                        let html = `
                                        <div class="card shadow-sm mb-5">
                                            <div class="card-header">
                                                <h3 class="card-title">Satuan Obat ${obatName}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-row-bordered table-row-dashed gy-4">
                                                        <thead>
                                                            <tr class="fw-bold fs-6 text-gray-800">
                                                                <th>Satuan</th>
                                                                <th>Stok Sistem</th>
                                                                <th>Stok Fisik</th>
                                                                <th>Keterangan</th>
                                                                <th>Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="satuan_items">
                                    `;

                        // Add rows for each unit
                        response.forEach(item => {
                            html += `
                                    <tr id="row-${item.satuan_id}" data-satuan-id="${item.satuan_id}" data-stok-sistem="${item.stok_sistem}">
                                        <td>${item.satuan_nama}</td>
                                        <td>${item.stok_sistem}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm stok-fisik-input" value="${item.stok_sistem}" min="0" step="any">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm keterangan-input" placeholder="Keterangan">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary add-satuan-btn" data-satuan-id="${item.satuan_id}">
                                                <i class="ki-duotone ki-plus-circle fs-2"></i> Tambahkan
                                            </button>
                                        </td>
                                    </tr>
                                `;
                        });

                        html += `
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-5">
                                        <button type="button" class="btn btn-success btn-sm" id="add_all_units" onclick="saveAllSatuanItems()">
                                            <i class="ki-duotone ki-check-circle fs-2"></i> Tambahkan Semua Satuan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Display the table
                        $('#detail_container').html(html);

                        // Add event handler for individual add buttons
                        $('.add-satuan-btn').on('click', function() {
                            const row = $(this).closest('tr');
                            const satuanId = row.data('satuan-id');
                            const stokSistem = row.data('stok-sistem');
                            const satuanNama = row.find('td:first').text().trim();

                            addObatToStockOpname(
                                obatId,
                                satuanId,
                                lokasiId,
                                stokSistem,
                                '', // Will be captured in the function
                                '', // Will be captured in the function
                                obatName,
                                satuanNama
                            );
                        });
                    } else {
                        $('#detail_container').html(`
                            <div class="alert alert-warning">
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-warning">Tidak ada satuan yang ditemukan</h4>
                                    <span>Obat ini tidak memiliki satuan yang terdaftar.</span>
                                </div>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error response:', xhr.responseText);

                    let errorMessage = 'Terjadi kesalahan saat mendapatkan detail stok';

                    if (xhr.status === 419) {
                        errorMessage = 'Sesi habis, silahkan refresh halaman';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    $('#detail_container').html(`
                        <div class="alert alert-danger">
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Error</h4>
                                <span>${errorMessage}</span>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Old loadSatuanTable function is removed and replaced with the direct approach above

        // Function to add obat to stock opname
        function addObatToStockOpname(obatId, satuanId, lokasiId, stokSistem, stokFisik, keterangan, obatNama,
            satuanNama) {
            // Always get the current values directly from the inputs when the function is called
            const row = $(`#row-${satuanId}`);
            const inputElement = row.find('.stok-fisik-input');
            const currentStokFisik = inputElement.val();
            const currentKeterangan = row.find('.keterangan-input').val();

            // Prepare data - make sure to use parseFloat for numeric values
            const data = {
                obat_id: obatId,
                satuan_id: satuanId,
                lokasi_id: lokasiId,
                stok_sistem: parseFloat(stokSistem) || 0,
                stok_fisik: parseFloat(currentStokFisik) || 0, // Use the current value from the input
                keterangan: currentKeterangan, // Use the current value from the input
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Add loading indicator to button
            const button = $(`button[data-satuan-id="${satuanId}"]`);
            button.html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambahkan...'
            );
            button.prop('disabled', true);

            // Debug log to check values before sending
            console.log(
                `Adding ${satuanNama} with stok_fisik: ${data.stok_fisik} (input value: ${inputElement.val()}), stok_sistem: ${data.stok_sistem}`
            );

            // Send request with explicit headers
            $.ajax({
                url: "{{ route('stock_opname.add_obat', $stockOpname) }}",
                method: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
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
                        toastr.error(response.message || 'Gagal menambahkan obat');
                        button.html('<i class="ki-duotone ki-plus-circle fs-2"></i> Tambahkan');
                        button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    button.html('<i class="ki-duotone ki-plus-circle fs-2"></i> Tambahkan');
                    button.prop('disabled', false);
                    console.error('Error response:', xhr.responseText);

                    if (xhr.status === 419) {
                        toastr.error('CSRF token mismatch. Coba refresh halaman dan coba lagi.');
                    } else if (xhr.responseJSON) {
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
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        // Use AJAX to delete the item
                        $.ajax({
                            url: "{{ route('stock_opname.remove_obat', ['stockOpname' => $stockOpname->id, 'detail' => ':detailId']) }}"
                                .replace(':detailId', detailId),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                resolve(response);
                            },
                            error: function(xhr, status, error) {
                                let errorMessage = 'Gagal menghapus obat';

                                if (xhr.status === 419) {
                                    errorMessage =
                                        'CSRF token mismatch. Coba refresh halaman dan coba lagi.';
                                } else if (xhr.responseJSON && xhr.responseJSON
                                    .message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.showValidationMessage(errorMessage);
                                reject(error);
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Obat berhasil dihapus dari stock opname.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Remove the item from the table
                    $(`#detail-${detailId}`).fadeOut(300, function() {
                        $(this).remove();

                        // Check if there are no more items in the table
                        if ($('#detail_items tr').length === 0) {
                            $('#detail_items').html(`<tr id="no-data-row">
                                <td colspan="8" class="text-center py-4">Belum ada data obat</td>
                            </tr>`);
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            // Debug CSRF token to make sure it's available
            console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));

            // Debug routes to make sure they're correct
            console.log('Search route:', "{{ route('stock_opname.search_obat') }}");
            console.log('Stock detail route:', "{{ route('stock_opname.get_stok_detail') }}");

            // Location is now hidden and automatically set

            // Initialize Select2 for obat with AJAX
            $('#obat_id').select2({
                placeholder: 'Pilih Obat',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('stock_opname.search_obat') }}",
                    dataType: 'json',
                    delay: 250,
                    method: 'GET',
                    cache: true,
                    data: function(params) {
                        console.log('Search params:', params);
                        return {
                            q: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        console.log('Received data:', data);

                        // Check for errors
                        if (data.error) {
                            toastr.error('Error: ' + data.error);
                            return {
                                results: []
                            };
                        }

                        // Return the results directly if in correct format
                        if (data.results) {
                            return data;
                        }

                        // Fallback for backward compatibility
                        return {
                            results: []
                        };
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr, status, error);
                        console.error('Error status:', xhr.status);
                        console.error('Response text:', xhr.responseText);

                        // Show detailed error message
                        let errorMsg = 'Error saat mencari obat';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg += ': ' + xhr.responseJSON.message;
                        } else if (error) {
                            errorMsg += ': ' + error;
                        }

                        toastr.error(errorMsg);
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
                },
                templateResult: formatObatResult,
                templateSelection: formatObatSelection,
                escapeMarkup: function(markup) {
                    return markup; // Allow HTML in results
                }
            });

            // Format result for Select2 obat search
            function formatObatResult(data) {
                if (data.loading) return data.text;
                if (!data.id) return data.text;

                try {
                    return $(`
                        <div class="d-flex flex-column p-1">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">${data.text}</span>
                            </div>
                        </div>
                    `);
                } catch (e) {
                    console.error("Error rendering obat template:", e);
                    return data.text;
                }
            }

            // Format selection for obat search
            function formatObatSelection(data) {
                if (!data.id) return data.text;
                return $(
                    `<span><i class="ki-duotone ki-capsule fs-4 me-2 text-primary"></i>${data.text}</span>`);
            }

            // Handle obat selection - show all units for the selected medicine
            $('#obat_id').on('select2:select', function(e) {
                const data = e.params.data;
                const obatId = data.id;
                const lokasiId = $('#lokasi_id').val();
                const obatName = data.text || '';

                console.log('Selected obat:', data);

                // Get and display all units for this medicine
                addSelectedMedicineToForm(obatId, obatName, lokasiId);

                // Change the add button text
                $('#btn_tambah_obat').html(
                    '<i class="ki-duotone ki-plus fs-2"></i> Tambahkan Obat Lain');

                // Don't clear the selection yet - let the user see what they selected
            }); // Form Update Validation
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
                        $('#btnComplete').attr('data-kt-indicator', 'on').prop('disabled',
                            true);
                        $('#formComplete').submit();
                    }
                });
            });

            // The resetForm function is defined globally now
            // The click handler is replaced by the onclick attribute

            // The saveAllSatuanItems function has been moved outside the document ready block to make it globally accessible

            // Initialize DataTable
            $('#details_table').DataTable({
                "paging": true,
                "pageLength": 10,
                "searching": true,
                "info": true,
                "language": {
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
                }
            });
        });
    </script>
@endpush
