@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Tambah Retur Pembelian</h3>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger m-5">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger m-5">
                {{ session('error') }}
            </div>
        @endif
        <div class="card-body py-4">
            <form id="form-retur" action="{{ route('retur_pembelian.store') }}" method="POST">
                @csrf

                <!-- Search Pembelian -->
                <div class="row mb-6">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">No Faktur Pembelian</label>
                            <select class="form-select" id="select2-faktur" data-placeholder="Pilih No Faktur" required>
                                <option></option>
                            </select>
                            <div class="form-text">Pilih nomor faktur pembelian yang ingin diretur</div>
                        </div>
                    </div>
                </div>

                <!-- Pembelian Details (will be shown after search) -->
                <div id="pembelian-details" style="display: none;">
                    <div class="separator separator-dashed my-5"></div>

                    <div class="row mb-6">
                        <input type="hidden" name="pembelian_id" id="pembelian-id">

                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="form-label">No Faktur</label>
                                <input type="text" class="form-control" id="no-faktur" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="form-label">Tanggal Faktur</label>
                                <input type="text" class="form-control" id="tanggal-faktur" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-5">
                                <label class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="supplier-nama" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Tanggal Retur</label>
                                <input type="date" class="form-control @error('tanggal_retur') is-invalid @enderror"
                                    name="tanggal_retur" value="{{ old('tanggal_retur', date('Y-m-d')) }}" required>
                                @error('tanggal_retur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-5">
                                <label class="form-label required">Alasan Retur</label>
                                <textarea class="form-control @error('alasan') is-invalid @enderror" name="alasan" rows="3" required>{{ old('alasan') }}</textarea>
                                @error('alasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <!-- Detail Items -->
                    <h4 class="mb-5">Detail Item Retur</h4>

                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                        <table class="table table-row-bordered" id="detail-table" style="min-width: 1200px;">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th style="min-width: 200px;">Obat</th>
                                    <th style="min-width: 120px;">Satuan</th>
                                    <th style="min-width: 120px;">No Batch</th>
                                    <th style="min-width: 120px;">Expired</th>
                                    <th style="min-width: 120px;">Lokasi</th>
                                    <th style="min-width: 100px;">Jumlah Awal</th>
                                    <th style="min-width: 120px;">Sudah Diretur</th>
                                    <th style="min-width: 120px;">Jumlah Retur</th>
                                    <th style="min-width: 120px;">Harga Beli</th>
                                    <th style="min-width: 150px;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="detail-tbody">
                                <tr id="empty-row">
                                    <td colspan="10" class="text-center text-muted">Belum ada detail pembelian yang
                                        dipilih</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('retur_pembelian.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary">Simpan Retur</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2 for invoice numbers
            $('#select2-faktur').select2({
                placeholder: 'Cari nomor faktur',
                minimumInputLength: 0,
                ajax: {
                    url: "{{ route('retur_pembelian.search_select2') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            _token: "{{ csrf_token() }}",
                            search: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.data,
                            pagination: {
                                more: data.pagination && data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    if (!data.id) {
                        return data.text;
                    }

                    let markup = `<div class="select2-result-repository clearfix">`;
                    markup +=
                        `<div class="select2-result-repository__title">${data.no_faktur || data.text}</div>`;

                    if (data.supplier) {
                        let supplierName = data.supplier.nama || '-';
                        let tanggal = '-';
                        try {
                            if (data.tanggal_faktur) {
                                tanggal = new Date(data.tanggal_faktur).toLocaleDateString('id-ID');
                            }
                        } catch (e) {
                            console.error("Date formatting error:", e);
                        }

                        markup += `<div class="select2-result-repository__description">
                                    Supplier: ${supplierName} |
                                    Tanggal: ${tanggal}
                                </div>`;
                    }

                    markup += `</div>`;

                    return markup;
                },
                templateSelection: function(data) {
                    if (data && data.no_faktur) {
                        return data.no_faktur;
                    }
                    return data.text || 'Pilih No Faktur';
                }
            });

            // Search for pembelian when select2 value changes
            $('#select2-faktur').on('select2:select', function(e) {
                const pembelianId = e.params.data.id;

                // Show loading
                const loadingHtml = `
                    <div class="d-flex justify-content-center my-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                $('#pembelian-details').html(loadingHtml).show();

                $.ajax({
                    url: "{{ route('retur_pembelian.search_by_id') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        pembelian_id: pembelianId
                    },
                    success: function(response) {
                        // Check for success
                        if (!response.success) {
                            Swal.fire({
                                title: 'Tidak ditemukan!',
                                text: response.message,
                                icon: 'warning',
                                confirmButtonText: 'Ok'
                            });
                            $('#pembelian-details').hide();
                            return;
                        }

                        // Regenerate the full HTML structure
                        $('#pembelian-details').html(`
                            <div class="separator separator-dashed my-5"></div>

                            <div class="row mb-6">
                                <input type="hidden" name="pembelian_id" id="pembelian-id">

                                <div class="col-md-4">
                                    <div class="mb-5">
                                        <label class="form-label">No Faktur</label>
                                        <input type="text" class="form-control" id="no-faktur" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-5">
                                        <label class="form-label">Tanggal Faktur</label>
                                        <input type="text" class="form-control" id="tanggal-faktur" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-5">
                                        <label class="form-label">Supplier</label>
                                        <input type="text" class="form-control" id="supplier-nama" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label required">Tanggal Retur</label>
                                        <input type="date" class="form-control" name="tanggal_retur" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label required">Alasan Retur</label>
                                        <textarea class="form-control" name="alasan" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-5"></div>

                            <!-- Detail Items -->
                            <h4 class="mb-5">Detail Item Retur</h4>
                            <div class="alert alert-info mb-5">
                                <div class="d-flex">
                                    <i class="ki-duotone ki-information-5 fs-2 me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <div>
                                        <strong>Petunjuk:</strong> Masukkan jumlah item yang ingin diretur pada kolom "Jumlah Retur". Jika tidak ingin meretur suatu item, biarkan nilai 0 atau kosong. Item yang sudah pernah diretur tidak bisa diretur kembali melebihi jumlah pembelian awal.
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                <table class="table table-row-bordered" id="detail-table" style="min-width: 1200px;">
                                    <thead>
                                        <tr class="fw-bold fs-6 text-gray-800">
                                            <th style="min-width: 200px;">Obat</th>
                                            <th style="min-width: 120px;">Satuan</th>
                                            <th style="min-width: 120px;">No Batch</th>
                                            <th style="min-width: 120px;">Expired</th>
                                            <th style="min-width: 120px;">Lokasi</th>
                                            <th style="min-width: 100px;">Jumlah Awal</th>
                                            <th style="min-width: 120px;">Sudah Diretur</th>
                                            <th style="min-width: 120px;">Jumlah Retur</th>
                                            <th style="min-width: 120px;">Harga Beli</th>
                                            <th style="min-width: 150px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail-tbody">
                                        <tr id="empty-row">
                                            <td colspan="10" class="text-center text-muted">Belum ada detail pembelian yang dipilih</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="separator separator-dashed my-5"></div>

                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('retur_pembelian.index') }}" class="btn btn-secondary">Batal</a>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-primary">Simpan Retur</button>
                                </div>
                            </div>
                        `);

                        // Populate pembelian details
                        const pembelian = response.pembelian;

                        $('#pembelian-id').val(pembelian.id);
                        $('#no-faktur').val(pembelian.no_faktur);

                        try {
                            $('#tanggal-faktur').val(new Date(pembelian.tanggal_faktur)
                                .toLocaleDateString('id-ID'));
                        } catch (e) {
                            $('#tanggal-faktur').val('-');
                            console.error("Date formatting error:", e);
                        }

                        $('#supplier-nama').val(pembelian.supplier ? pembelian.supplier.nama :
                            '-');

                        // Debug response
                        console.log("Pembelian data:", pembelian);
                        console.log("Details count:", pembelian.details ? pembelian.details
                            .length : 0);

                        // Populate detail items
                        populateDetailItems(pembelian.details);
                    },
                    error: function(xhr) {
                        $('#pembelian-details').hide();

                        console.error("AJAX error:", xhr);

                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat mencari data pembelian: ' + (
                                xhr.responseJSON && xhr.responseJSON.message ? xhr
                                .responseJSON.message : 'Silahkan coba lagi'),
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    }
                });
            });

            // Calculate total when input jumlah retur changes
            $(document).on('input', '.jumlah-retur-input', function() {
                const row = $(this).closest('tr');
                const maxQty = parseInt(row.data('max-qty'));
                let qty = parseInt($(this).val()) || 0;

                // Ensure quantity is not greater than max
                if (qty > maxQty) {
                    qty = maxQty;
                    $(this).val(maxQty);
                }

                if (qty < 0) {
                    qty = 0;
                    $(this).val(0);
                }

                const hargaBeli = parseFloat(row.data('harga-beli'));
                const total = qty * hargaBeli;

                row.find('.total-display').text('Rp ' + formatRupiah(total));
            });

            // Form submission validation
            $('#form-retur').on('submit', function(e) {
                // Check if any items have a retur quantity
                let hasReturItems = false;

                $('.jumlah-retur-input').each(function() {
                    const qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        hasReturItems = true;
                        return false; // Break the loop
                    }
                });

                if (!hasReturItems) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Minimal satu item harus memiliki jumlah retur!',
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                    return false;
                }

                // Show loading on submit button
                const submitBtn = $(this).find('[type="submit"]');
                submitBtn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...'
                );
                submitBtn.attr('disabled', true);

                return true;
            });

            // Function to populate detail items
            function populateDetailItems(details) {
                const tbody = $('#detail-tbody');
                tbody.empty();

                // Debug to console
                console.log("Populating items, details:", details);

                if (!details || details.length === 0) {
                    tbody.append(
                        '<tr><td colspan="10" class="text-center text-muted">Tidak ada item yang dapat diretur</td></tr>'
                    );
                    return;
                }

                let validItemsCount = 0;

                details.forEach(function(detail, index) {
                    try {
                        // Safeguard against missing properties
                        if (!detail || !detail.obat) {
                            console.error("Invalid detail item:", detail);
                            return; // Skip this item
                        }

                        // Calculate returned quantity
                        const returnedQty = detail.returned_qty || 0; // From controller
                        const remainingQty = detail.remaining_qty || detail
                            .jumlah; // Use remaining_qty from controller or default to full jumlah

                        // Get location safely
                        let lokasiId = '';
                        let lokasiNama = '-';
                        if (detail.stok && detail.stok.length > 0 && detail.stok[0]) {
                            lokasiId = detail.stok[0].lokasi_id || '';
                            lokasiNama = detail.stok[0].lokasi && detail.stok[0].lokasi.nama ? detail.stok[
                                0].lokasi.nama : '-';
                        }

                        // Format dates safely
                        let expiredDate = '-';
                        try {
                            if (detail.tanggal_expired) {
                                expiredDate = new Date(detail.tanggal_expired).toLocaleDateString('id-ID');
                            }
                        } catch (e) {
                            console.error("Date parsing error:", e);
                        }

                        // Create row for all items, but disable input if remaining_qty is 0
                        const row = `
                        <tr data-detail-id="${detail.id}" data-max-qty="${remainingQty}" data-harga-beli="${detail.harga_beli}">
                            <input type="hidden" name="detail[${index}][pembelian_detail_id]" value="${detail.id}">
                            <input type="hidden" name="detail[${index}][obat_id]" value="${detail.obat_id}">
                            <input type="hidden" name="detail[${index}][satuan_id]" value="${detail.satuan_id}">
                            <input type="hidden" name="detail[${index}][no_batch]" value="${detail.no_batch}">
                            <input type="hidden" name="detail[${index}][lokasi_id]" value="${lokasiId}">
                            <td>${detail.obat.nama_obat}</td>
                            <td>${detail.satuan && detail.satuan.nama ? detail.satuan.nama : '-'}</td>
                            <td>${detail.no_batch || '-'}</td>
                            <td>${expiredDate}</td>
                            <td>${lokasiNama}</td>
                            <td>${detail.jumlah}</td>
                            <td>${returnedQty}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm jumlah-retur-input"
                                    name="detail[${index}][jumlah]" min="0" max="${remainingQty}" value="0"
                                    ${remainingQty <= 0 ? 'disabled' : ''}>
                                ${remainingQty <= 0 ? '<div class="form-text text-danger">Sudah diretur semua</div>' : ''}
                            </td>
                            <td>Rp ${formatRupiah(detail.harga_beli)}</td>
                            <td class="total-display">Rp 0</td>
                        </tr>
                        `;

                        tbody.append(row);
                        validItemsCount++;
                    } catch (error) {
                        console.error("Error rendering item:", error, detail);
                    }
                });

                if (validItemsCount === 0) {
                    tbody.append(
                        '<tr><td colspan="10" class="text-center text-muted">Tidak ada item yang dapat diretur</td></tr>'
                    );
                }
            }

            // Function to format numbers as rupiah
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }
        });
    </script>
@endpush
