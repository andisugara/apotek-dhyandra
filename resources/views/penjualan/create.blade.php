@extends('layout.app')

@section('title', 'Tambah Penjualan')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title">Tambah Penjualan</h3>
            <div class="card-toolbar">
                <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-light-primary">
                    <i class="ki-outline ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form id="penjualanForm" action="{{ route('penjualan.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Left side - Form fields -->
                    <div class="col-md-7">
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header">
                                <div class="card-title">
                                    <h4>Data Obat</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-5">
                                    <div class="col-12">
                                        <label class="form-label">Cari Obat</label>
                                        <select id="obatSelect" class="form-select"></select>
                                        <div class="form-text text-muted">Ketik untuk mencari nama atau kode obat. Hasil
                                            akan menampilkan detail stok, satuan dan lokasi.</div>
                                    </div>
                                </div>

                                <div class="row mt-5">
                                    <div class="col-12">
                                        <h4>Daftar Item</h4>
                                        <div class="table-responsive">
                                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                                <thead>
                                                    <tr class="fw-bold text-muted bg-light">
                                                        <th>Obat</th>
                                                        <th>Satuan</th>
                                                        <th>Batch</th>
                                                        <th>Harga</th>
                                                        <th>Jumlah</th>
                                                        <th>Diskon</th>
                                                        <th>Subtotal</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="cartItems">
                                                    <!-- Items will be added here -->
                                                    <tr id="emptyCart">
                                                        <td colspan="8" class="text-center">Belum ada item yang
                                                            ditambahkan</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right side - Info and Payment -->
                    <div class="col-md-5">
                        <!-- Transaction Info Card -->
                        <div class="card mb-5 mb-xl-10">
                            <div class="card-header">
                                <div class="card-title">
                                    <h4>Informasi Transaksi</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label required">Tanggal Penjualan</label>
                                        <input type="date" class="form-control" name="tanggal_penjualan"
                                            value="{{ old('tanggal_penjualan', date('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Pasien</label>
                                        <select id="pasienSelect" class="form-select" name="pasien_id">
                                            <option></option>
                                            @foreach ($pasiens as $pasien)
                                                <option value="{{ $pasien->id }}"
                                                    {{ old('pasien_id') == $pasien->id ? 'selected' : '' }}>
                                                    {{ $pasien->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label required">Jenis Pembayaran</label>
                                        <div class="d-flex">
                                            <div class="form-check form-check-custom form-check-solid me-5">
                                                <input class="form-check-input" type="radio" name="jenis"
                                                    id="jenisTunai" value="TUNAI"
                                                    {{ old('jenis', 'TUNAI') == 'TUNAI' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="jenisTunai">
                                                    Tunai
                                                </label>
                                            </div>
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="radio" name="jenis"
                                                    id="jenisNonTunai" value="NON_TUNAI"
                                                    {{ old('jenis') == 'NON_TUNAI' ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="jenisNonTunai">
                                                    Non Tunai
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Keterangan</label>
                                        <textarea class="form-control" name="keterangan" rows="2">{{ old('keterangan') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Card -->
                        <div class="card mb-5 mb-xl-10 position-sticky" style="top: 100px;">
                            <div class="card-header">
                                <div class="card-title">
                                    <h4>Pembayaran</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Subtotal</label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span id="subtotalDisplay">Rp 0</span>
                                        <input type="hidden" name="subtotal" id="subtotal" value="0">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Diskon</label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span id="diskonDisplay">Rp 0</span>
                                        <input type="hidden" name="diskon_total" id="diskon_total" value="0">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label">PPN <small class="text-muted">(sudah
                                                termasuk)</small></label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span id="ppnDisplay">Rp 0</span>
                                        <input type="hidden" name="ppn_total" id="ppn_total" value="0">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-6">
                                        <label class="form-label fw-bold fs-4">Grand Total</label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span id="grandTotalDisplay" class="fw-bold fs-4">Rp 0</span>
                                        <input type="hidden" name="grand_total" id="grand_total" value="0">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label required">Bayar</label>
                                        <input type="text" class="form-control" name="bayar" id="bayar"
                                            required value="0">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Kembalian</label>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span id="kembalianDisplay" class="fw-bold">Rp 0</span>
                                        <input type="hidden" name="kembalian" id="kembalian" value="0">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="btn btn-primary w-100" id="btnSave">Simpan
                                        Transaksi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Item Template (hidden) -->
    <template id="itemTemplate">
        <tr class="item-row">
            <td>
                <input type="hidden" name="detail[__index__][obat_id]" class="obat-id">
                <input type="hidden" name="detail[__index__][lokasi_id]" class="lokasi-id">
                <input type="hidden" name="detail[__index__][no_batch]" class="batch">
                <div class="d-flex flex-column">
                    <div class="fw-bold obat-nama"></div>
                    <div class="text-muted obat-kode"></div>
                </div>
            </td>
            <td>
                <input type="hidden" name="detail[__index__][satuan_id]" class="satuan-id">
                <span class="satuan-nama"></span>
            </td>
            <td>
                <span class="batch-display"></span>
            </td>
            <td>
                <input type="hidden" name="detail[__index__][harga]" class="harga">
                <span class="harga-display"></span>
            </td>
            <td>
                <input type="number" name="detail[__index__][jumlah]" class="form-control form-control-sm jumlah"
                    min="1" value="1">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="detail[__index__][diskon_persen]"
                        class="form-control form-control-sm diskon-persen" min="0" max="100" value="0"
                        placeholder="%">
                    <input type="hidden" name="detail[__index__][diskon]" class="diskon" value="0">
                    <span class="diskon-display form-text w-100 mt-1">Rp 0</span>
                </div>
            </td>
            <td>
                <input type="hidden" name="detail[__index__][subtotal]" class="item-subtotal">
                <input type="hidden" name="detail[__index__][ppn]" class="ppn" value="0">
                <input type="hidden" name="detail[__index__][total]" class="total">
                <span class="total-display"></span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-icon btn-light-danger btn-active-danger remove-item">
                    <i class="ki-outline ki-trash fs-2"></i>
                </button>
            </td>
        </tr>
    </template>

    <!-- Batch Selection Modal -->
    <div class="modal fade" id="batchModal" tabindex="-1" aria-labelledby="batchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="batchModalLabel">Pilih Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th>No Batch</th>
                                    <th>Tanggal Expired</th>
                                    <th>Lokasi</th>
                                    <th>Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="batchTableBody">
                                <!-- Batch options will be displayed here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Load Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Fallback untuk Select2 jika gagal dimuat -->
    <script>
        if (typeof $.fn.select2 === 'undefined') {
            document.write(
                '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"><\/script>');
            console.warn("Primary Select2 load failed, loading fallback...");
        }
    </script>

    <!-- Input Mask untuk format uang -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>

    <style>
        /* Select2 styling */
        .select2-container--default .select2-selection--single {
            height: 41px;
            padding: 0.5rem 1rem;
            border-radius: 0.475rem;
            border: 1px solid var(--bs-gray-300);
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--bs-primary);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
            padding-left: 0;
            color: var(--bs-gray-700);
        }

        .select2-dropdown {
            border: 1px solid var(--bs-gray-300);
            border-radius: 0.475rem;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.075);
        }

        .select2-search--dropdown .select2-search__field {
            padding: 0.5rem;
            border: 1px solid var(--bs-gray-300);
            border-radius: 0.475rem;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            display: flex;
            align-items: center;
        }

        /* Fix z-index for dropdowns */
        .select2-container--open .select2-dropdown {
            z-index: 1056 !important;
        }

        /* Result item styling */
        .select2-result-obat {
            padding: 10px;
            border-bottom: 1px solid var(--bs-gray-200);
        }

        .select2-result-obat:last-child {
            border-bottom: none;
        }
    </style>

    <script>
        // Global variables (di luar scope document.ready agar bisa diakses oleh semua fungsi)
        let itemCount = 0;
        let selectedObat = null;

        $(document).ready(function() {

            // Initialize Select2 for Pasien
            $('#pasienSelect').select2({
                placeholder: 'Pilih pasien',
                allowClear: true,
                width: '100%',
                templateResult: function(data) {
                    if (!data.id) return data.text;
                    return $(`<div class="d-flex align-items-center">
                                <i class="ki-outline ki-profile-user fs-4 me-2 text-primary"></i>
                                <div>${data.text}</div>
                            </div>`);
                },
                templateSelection: function(data) {
                    if (!data.id) return data.text;
                    return $(`<div class="d-flex align-items-center">
                                <i class="ki-outline ki-profile-user fs-4 me-2"></i>
                                <span>${data.text}</span>
                            </div>`);
                }
            });

            // Initialize Select2 for Obat Search with AJAX
            $('#obatSelect').select2({
                placeholder: 'Ketik nama atau kode obat...',
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('penjualan.search_obat') }}",
                    dataType: 'json',
                    delay: 250,
                    method: 'POST',
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        return data;
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: formatObatResult,
                templateSelection: function(data) {
                    if (!data.id) return data.text;
                    return $(`<div class="d-flex align-items-center">
                                <i class="ki-outline ki-pill fs-4 me-2 text-success"></i>
                                <span>${data.text}</span>
                            </div>`);
                }
            });

            // Format result for Select2 obat search
            function formatObatResult(data) {
                if (data.loading) return data.text;

                if (!data.obat) return data.text;

                try {
                    const obat = data.obat;
                    const totalStock = obat.total_stok || 0;

                    // Get satuan info safely
                    const satuanName = obat.satuan ? obat.satuan.nama_satuan : 'N/A';

                    // Get kategori and golongan safely
                    const kategori = obat.kategori || 'N/A';
                    const golongan = obat.golongan || 'N/A';

                    // Get price from satuan
                    let harga = 0;
                    if (obat.satuan) {
                        harga = obat.satuan.harga_jual || 0;
                    }

                    // Get batch info
                    const noBatch = obat.first_stock ? obat.first_stock.no_batch : '-';
                    const expiredDate = obat.first_stock ? formatDate(obat.first_stock.tanggal_expired) : '-';

                    return $(`
                        <div class="d-flex flex-column p-2">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fs-5 fw-bold">${obat.nama_obat || 'Tidak ada nama'}</span>
                                <span class="badge badge-light-primary">${obat.kode_obat || '-'}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge badge-light-success">Stok: ${totalStock}</span>
                                <span class="badge badge-light-warning">Satuan: ${satuanName}</span>
                                <span class="badge badge-light-info">Kategori: ${kategori}</span>
                                <span class="badge badge-light-danger">Golongan: ${golongan}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>Batch: ${noBatch} (Exp: ${expiredDate})</div>
                                <div class="fw-bold">Rp ${formatNumber(harga)}</div>
                            </div>
                        </div>
                    `);
                } catch (e) {
                    console.error("Error rendering template:", e);
                    return data.text || "Error displaying item";
                }
            }

            // Handle Obat selection - directly add to cart with FIFO stock
            $('#obatSelect').on('select2:select', function(e) {
                try {
                    const selectedData = e.params.data;
                    if (selectedData && selectedData.obat) {
                        selectedObat = selectedData.obat;

                        // Check if we have first_stock available
                        if (selectedObat.first_stock) {
                            // Add directly to cart using the first_stock (FIFO)
                            addItemToCart(selectedObat, selectedObat.first_stock);
                        } else {
                            console.error("No stock info available");
                            alert("Informasi stok tidak tersedia untuk produk ini");
                        }

                        // Reset select for next selection
                        $(this).val(null).trigger('change');
                    } else {
                        console.error("Selected data is missing obat property:", selectedData);
                        alert("Data obat tidak lengkap, silakan coba lagi");
                    }
                } catch (error) {
                    console.error("Error in select handler:", error);
                    alert("Terjadi kesalahan saat memilih obat");
                }
            });

            // Format currency inputs
            $('#bayar').inputmask('currency', {
                radixPoint: ',',
                groupSeparator: '.',
                digits: 0,
                prefix: 'Rp '
            }); // Update kembalian when payment amount changes
            $('#bayar').on('change', function() {
                calculateKembalian();
            });

            // Remove item from cart
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                updateCartStatus();
                calculateTotals();
            });

            // Update totals when quantity changes
            $(document).on('change', '.jumlah, .diskon-persen', function() {
                calculateRowTotal($(this).closest('tr'));
            }); // Form submission validation
            $('#penjualanForm').on('submit', function(e) {
                if ($('#cartItems .item-row').length === 0) {
                    e.preventDefault();
                    alert('Tambahkan minimal 1 item untuk melanjutkan');
                    return false;
                }

                // Ensure grand total is greater than zero
                if (parseFloat($('#grand_total').val()) <= 0) {
                    e.preventDefault();
                    alert('Total transaksi harus lebih dari 0');
                    return false;
                }

                // For cash payments, ensure payment is enough
                if ($('input[name="jenis"]:checked').val() === 'TUNAI') {
                    const grandTotal = parseFloat($('#grand_total').val());
                    const bayar = parseCurrency($('#bayar').val());

                    if (bayar < grandTotal) {
                        e.preventDefault();
                        alert('Jumlah pembayaran kurang dari total transaksi');
                        return false;
                    }
                }

                return true;
            });

            // Handle batch selection from modal
            $(document).on('click', '.btn-select-batch', function() {
                try {
                    const stockData = JSON.parse($(this).attr('data-stock'));
                    addItemToCart(selectedObat, stockData);
                    $('#batchModal').modal('hide');
                } catch (error) {
                    console.error("Error parsing stock data:", error);
                    alert("Terjadi kesalahan saat memilih batch");
                }
            });
        });

        // This function is no longer needed as we're directly adding to cart
        // Keeping it as a fallback if needed
        function handleObatSelection(obat) {
            // Get the batch details for this obat
            $.ajax({
                url: "{{ route('penjualan.get_stok_detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    obat_id: obat.id,
                    satuan_id: obat.satuan_id
                },
                success: function(data) {
                    if (data.length > 0) {
                        // Use the first batch (FIFO)
                        addItemToCart(obat, data[0]);
                    } else {
                        alert('Tidak ada stok tersedia untuk obat ini.');
                    }
                },
                error: function(err) {
                    console.error("Error getting stock details:", err);
                    alert('Gagal mendapatkan informasi stok. Silakan coba lagi.');
                }
            });
        }

        // This function is no longer needed as we're not showing batch selection modal
        // Keeping it as a reference in case it's needed later
        function showBatchSelectionModal(stockData) {
            // This function is kept for future reference but currently not used
            console.log("Batch selection not needed - using FIFO");
        }

        // Add item to cart - simplified for direct add from search
        function addItemToCart(obat, stock) {
            // Check if already in cart (comparing obat_id AND satuan_id for uniqueness)
            let exists = false;
            $('#cartItems .item-row').each(function() {
                const obatId = $(this).find('.obat-id').val();
                const satuanId = $(this).find('.satuan-id').val();
                const batch = $(this).find('.batch').val();

                // Only consider it a duplicate if same obat, satuan AND batch
                if (obatId == obat.id && satuanId == stock.satuan_id && batch == stock.no_batch) {
                    // Increment quantity instead of adding new row
                    const qtyInput = $(this).find('.jumlah');
                    qtyInput.val(parseInt(qtyInput.val()) + 1).change();
                    exists = true;
                    return false;
                }
            });

            if (exists) {
                return;
            }

            // Get template and replace placeholders
            const template = $('#itemTemplate').html();
            const newRow = $(template.replace(/__index__/g, itemCount));

            // Fill in obat values
            newRow.find('.obat-id').val(obat.id);
            newRow.find('.obat-nama').text(obat.nama_obat || 'Tidak ada nama');
            newRow.find('.obat-kode').text(obat.kode_obat || '-');

            // Set satuan information
            if (obat.satuan) {
                // Use the satuan from the obat data
                newRow.find('.satuan-id').val(obat.satuan.id);
                newRow.find('.satuan-nama').text(obat.satuan.nama_satuan || 'Default');
            } else {
                // Default if no satuan info available
                newRow.find('.satuan-id').val(stock.satuan_id || '');
                newRow.find('.satuan-nama').text(stock.satuan ? stock.satuan.nama_satuan : 'Default');
            }

            // Set batch and location information
            newRow.find('.batch').val(stock.no_batch || '');
            newRow.find('.batch-display').text(stock.no_batch || '-');
            newRow.find('.lokasi-id').val(stock.lokasi_id || '');

            // Get price - prefer stock price if available
            let harga = parseFloat(stock.harga || 0);

            // If stock price isn't available, try to get from obat.satuan
            if (harga === 0 && obat.satuan) {
                harga = parseFloat(obat.satuan.harga_jual || 0);
            }

            // Update price display
            newRow.find('.harga').val(harga);
            newRow.find('.harga-display').text('Rp ' + formatNumber(harga));

            // Add to cart
            $('#emptyCart').hide();
            $('#cartItems').append(newRow);

            // Calculate values for new row
            calculateRowTotal(newRow);

            // Increment counter for next item
            itemCount++;

            // Recalculate totals
            calculateTotals();
        }

        // Calculate totals for a specific row
        function calculateRowTotal(row) {
            const quantity = parseInt(row.find('.jumlah').val() || 1);
            const harga = parseFloat(row.find('.harga').val() || 0);
            const diskonPersen = parseFloat(row.find('.diskon-persen').val() || 0);

            // Calculate values
            const subtotal = quantity * harga;
            const diskon = (diskonPersen / 100) * subtotal;
            const total = subtotal - diskon;

            // Update hidden inputs
            row.find('.item-subtotal').val(subtotal.toFixed(2));
            row.find('.diskon').val(diskon.toFixed(2));
            row.find('.ppn').val(0); // PPN sudah termasuk di harga jual
            row.find('.total').val(total.toFixed(2));

            // Update display
            row.find('.diskon-display').text('Rp ' + formatNumber(diskon));
            row.find('.total-display').text('Rp ' + formatNumber(total));

            return {
                subtotal,
                diskon,
                total
            };
        } // Update cart status
        function updateCartStatus() {
            if ($('#cartItems .item-row').length === 0) {
                $('#emptyCart').show();
            } else {
                $('#emptyCart').hide();
            }
        }

        // Calculate all totals
        function calculateTotals() {
            let subtotal = 0;
            let totalDiskon = 0;
            let totalPpn = 0; // Keeping this for compatibility, but will be zero

            $('#cartItems .item-row').each(function() {
                subtotal += parseFloat($(this).find('.item-subtotal').val() || 0);
                totalDiskon += parseFloat($(this).find('.diskon').val() || 0);
                // PPN already included in price, so no need to add it
            });

            const grandTotal = subtotal - totalDiskon;

            // Update totals display
            $('#subtotal').val(subtotal.toFixed(2));
            $('#subtotalDisplay').text('Rp ' + formatNumber(subtotal));

            $('#diskon_total').val(totalDiskon.toFixed(2));
            $('#diskonDisplay').text('Rp ' + formatNumber(totalDiskon));

            // Keep PPN display but set to 0 as it's already included in price
            $('#ppn_total').val('0.00');
            $('#ppnDisplay').text('Rp 0'); // PPN already included in prices

            $('#grand_total').val(grandTotal.toFixed(2));
            $('#grandTotalDisplay').text('Rp ' + formatNumber(grandTotal));

            // Recalculate kembalian
            calculateKembalian();
        }

        // Calculate kembalian
        function calculateKembalian() {
            const grandTotal = parseFloat($('#grand_total').val() || 0);
            const bayar = parseCurrency($('#bayar').val() || 0);
            const kembalian = Math.max(0, bayar - grandTotal);

            $('#kembalian').val(kembalian.toFixed(2));
            $('#kembalianDisplay').text('Rp ' + formatNumber(kembalian));
        }

        // Format number as currency
        function formatNumber(number) {
            // Handle potential NaN or undefined
            if (isNaN(number) || number === undefined || number === null) {
                console.warn("Invalid number passed to formatNumber:", number);
                return "0";
            }
            return Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Parse currency string back to number
        function parseCurrency(str) {
            // Handle potential invalid inputs
            if (!str || typeof str !== 'string') {
                console.warn("Invalid currency string:", str);
                return 0;
            }
            return parseFloat(str.replace(/[^\d,.-]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
        }

        // Format date
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                // Check if date is valid
                if (isNaN(date.getTime())) {
                    return '-';
                }
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (e) {
                console.warn("Error formatting date:", dateStr, e);
                return '-';
            }
        }
    </script>
@endpush
