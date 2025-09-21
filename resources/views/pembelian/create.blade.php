@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Tambah Pembelian</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <form id="form-pembelian" action="{{ route('pembelian.store') }}" method="POST">
                @csrf

                <!-- Data Pembelian -->
                <div class="row mb-6">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label">No Purchase Order</label>
                            <input type="text" class="form-control @error('no_po') is-invalid @enderror" name="no_po"
                                value="{{ old('no_po') }}" placeholder="No PO (opsional)">
                            @error('no_po')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">No Faktur</label>
                            <input type="text" class="form-control @error('no_faktur') is-invalid @enderror"
                                name="no_faktur" value="{{ old('no_faktur') }}" placeholder="No Faktur" required>
                            @error('no_faktur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-6">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Tanggal Faktur</label>
                            <input type="date" class="form-control @error('tanggal_faktur') is-invalid @enderror"
                                name="tanggal_faktur" value="{{ old('tanggal_faktur', date('Y-m-d')) }}" required>
                            @error('tanggal_faktur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Supplier</label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" name="supplier_id"
                                data-control="select2" data-placeholder="Pilih supplier" required>
                                <option></option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-6">
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="form-label required">Jenis Pembayaran</label>
                            <select class="form-select @error('jenis') is-invalid @enderror" name="jenis"
                                id="jenis-pembayaran" required>
                                <option value="TUNAI" {{ old('jenis') == 'TUNAI' ? 'selected' : '' }}>TUNAI</option>
                                <option value="HUTANG" {{ old('jenis') == 'HUTANG' ? 'selected' : '' }}>HUTANG</option>
                                <option value="KONSINYASI" {{ old('jenis') == 'KONSINYASI' ? 'selected' : '' }}>KONSINYASI
                                </option>
                            </select>
                            @error('jenis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 tunai-section">
                        <div class="mb-5">
                            <label class="form-label required">Akun Kas</label>
                            <select class="form-select @error('akun_kas_id') is-invalid @enderror" name="akun_kas_id"
                                data-control="select2" data-placeholder="Pilih akun kas">
                                <option></option>
                                @foreach ($akuns as $akun)
                                    <option value="{{ $akun->id }}"
                                        {{ old('akun_kas_id') == $akun->id ? 'selected' : '' }}>{{ $akun->kode }} -
                                        {{ $akun->nama }}</option>
                                @endforeach
                            </select>
                            @error('akun_kas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4 hutang-section" style="display: none;">
                        <div class="mb-5">
                            <label class="form-label required">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror"
                                name="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo') }}">
                            @error('tanggal_jatuh_tempo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- Summary -->
                <div class="row mb-6 justify-content-end">
                    <div class="col-md-5">
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label text-end">Subtotal:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm text-end" id="subtotal-display"
                                    disabled>
                                <input type="hidden" name="subtotal" id="subtotal-input">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label text-end">Diskon Total:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm text-end"
                                    id="diskon-total-display" disabled>
                                <input type="hidden" name="diskon_total" id="diskon-total-input">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label text-end">PPN:</label>
                            <div class="col-sm-8">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm text-end" name="ppn_total"
                                        id="ppn-total" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label fw-bolder text-end">Grand Total:</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control form-control-sm text-end fw-bolder"
                                    id="grand-total-display" disabled>
                                <input type="hidden" name="grand_total" id="grand-total-input">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-5"></div>

                <!-- Detail Obat -->
                <h4 class="mb-5">Detail Pembelian</h4>

                <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                    <table class="table table-row-bordered" id="detail-table" style="min-width: 1800px;">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800">
                                <th style="min-width: 200px;">Obat</th>
                                <th style="min-width: 140px;">Satuan</th>
                                <th style="min-width: 120px;">Jumlah</th>
                                <th style="min-width: 150px;">Harga Beli</th>
                                <th style="min-width: 150px;">Subtotal</th>
                                <th style="min-width: 100px;">Diskon %</th>
                                <th style="min-width: 120px;">Diskon Rp</th>
                                <th style="min-width: 150px;">HPP</th>
                                <th style="min-width: 100px;">Margin %</th>
                                <th style="min-width: 150px;">Harga Jual</th>
                                <th style="min-width: 120px;">No Batch</th>
                                <th style="min-width: 120px;">Expired</th>
                                <th style="min-width: 150px;">Lokasi</th>
                                <th style="min-width: 150px;">Total</th>
                                <th style="min-width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="detail-tbody">
                            <tr id="empty-row">
                                <td colspan="15" class="text-center text-muted">Belum ada detail pembelian</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="15">
                                    <button type="button" class="btn btn-sm btn-light-primary" id="add-detail-btn">
                                        <i class="ki-duotone ki-plus fs-5"></i>Tambah Item
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>



                <div class="separator separator-dashed my-5"></div>

                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Template untuk row detail -->
    <template id="detail-row-template">
        <tr class="detail-row">
            <td>
                <select class="form-select form-select-sm obat-select" name="detail[__index__][obat_id]" required
                    data-index="__index__">
                    <option value="">Pilih Obat</option>
                    @foreach ($obats as $obat)
                        <option value="{{ $obat->id }}">{{ $obat->kode_obat }} - {{ $obat->nama_obat }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm satuan-select" name="detail[__index__][satuan_id]" required
                    disabled>
                    <option value="">Pilih Satuan</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm jumlah-input" name="detail[__index__][jumlah]"
                    value="1" min="1" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm harga-beli-input text-end"
                    name="detail[__index__][harga_beli]" value="0" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm subtotal-item-display text-end" disabled>
                <input type="hidden" class="subtotal-item-input" name="detail[__index__][subtotal]" value="0">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm diskon-persen-input"
                    name="detail[__index__][diskon_persen]" value="0" min="0" max="100"
                    step="0.01">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm diskon-nominal-display text-end" disabled>
                <input type="hidden" class="diskon-nominal-input" name="detail[__index__][diskon_nominal]"
                    value="0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm hpp-display text-end" disabled>
                <input type="hidden" class="hpp-input" name="detail[__index__][hpp]" value="0">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm margin-jual-input"
                    name="detail[__index__][margin_jual_persen]" value="10" min="0" step="0.01">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm harga-jual-display text-end" disabled>
                <input type="hidden" class="harga-jual-input" name="detail[__index__][harga_jual_per_unit]"
                    value="0">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="detail[__index__][no_batch]" required>
            </td>
            <td>
                <input type="date" class="form-control form-control-sm" name="detail[__index__][tanggal_expired]"
                    required>
            </td>
            <td>
                <select class="form-select form-select-sm" name="detail[__index__][lokasi_id]" required>
                    <option value="">Pilih Lokasi</option>
                    @foreach ($lokasis as $lokasi)
                        <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm total-item-display text-end" disabled>
                <input type="hidden" class="total-item-input" name="detail[__index__][total]" value="0">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-icon btn-light-danger delete-row-btn">
                    <i class="ki-solid ki-abstract-11 fs-5"></i>
                </button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let detailIndex = 0;

            // Initialize Select2
            initSelect2();

            // Add detail row on button click - Place before validator initialization
            $('#add-detail-btn').on('click', function() {
                console.log('Add detail button clicked');
                addDetailRow();
            });

            // Initialize jQuery validate
            initValidator();

            // Show/hide payment-specific fields
            $('#jenis-pembayaran').on('change', function() {
                const jenis = $(this).val();

                if (jenis === 'TUNAI') {
                    $('.tunai-section').show();
                    $('.hutang-section').hide();

                    // Update required attributes for HTML5 validation
                    $('select[name="akun_kas_id"]').prop('required', true);
                    $('input[name="tanggal_jatuh_tempo"]').prop('required', false);
                } else if (jenis === 'HUTANG') {
                    $('.tunai-section').hide();
                    $('.hutang-section').show();

                    // Update required attributes for HTML5 validation
                    $('select[name="akun_kas_id"]').prop('required', false);
                    $('input[name="tanggal_jatuh_tempo"]').prop('required', true);
                } else {
                    $('.tunai-section').hide();
                    $('.hutang-section').hide();

                    // Update required attributes for HTML5 validation
                    $('select[name="akun_kas_id"]').prop('required', false);
                    $('input[name="tanggal_jatuh_tempo"]').prop('required', false);
                }
            });

            // Trigger change event on page load
            $('#jenis-pembayaran').trigger('change');

            // Delete detail row
            $(document).on('click', '.delete-row-btn', function() {
                $(this).closest('tr').remove();
                updateEmptyRowVisibility();
                calculateTotals();
            });

            // Change obat (load satuan)
            $(document).on('change', '.obat-select', function() {
                const obatId = $(this).val();
                const index = $(this).data('index');
                const satuanSelect = $(this).closest('tr').find('.satuan-select');

                if (obatId) {
                    // Disable and show loading
                    satuanSelect.prop('disabled', true);
                    satuanSelect.html('<option value="">Loading...</option>');

                    // Fetch satuan data
                    $.ajax({
                        url: `/pembelian/obat-satuans/${obatId}`,
                        method: 'GET',
                        success: function(response) {
                            // Create options
                            let options = '<option value="">Pilih Satuan</option>';
                            response.forEach(function(satuan) {
                                options +=
                                    `<option value="${satuan.satuan_id}">${satuan.satuan.nama}</option>`;
                            });

                            // Update select with options and enable it
                            satuanSelect.html(options);
                            satuanSelect.prop('disabled', false);
                        },
                        error: function() {
                            // Reset on error
                            satuanSelect.html('<option value="">Pilih Satuan</option>');
                            satuanSelect.prop('disabled', false);
                        }
                    });
                } else {
                    // Reset if no obat selected
                    satuanSelect.html('<option value="">Pilih Satuan</option>');
                    satuanSelect.prop('disabled', true);
                }

                // Validate the field
                $("#form-pembelian").validate().element(this);
            });

            // Calculate subtotal when quantity or price changes
            $(document).on('input', '.jumlah-input, .harga-beli-input', function() {
                const row = $(this).closest('tr');
                calculateRowValues(row);
            });

            // Calculate discount when percentage changes
            $(document).on('input', '.diskon-persen-input', function() {
                const row = $(this).closest('tr');
                calculateRowValues(row);
            });

            // Calculate profit when margin changes
            $(document).on('input', '.margin-jual-input', function() {
                const row = $(this).closest('tr');
                calculateRowValues(row);
            });

            // Calculate PPN when value changes and update HPP for all rows
            $('#ppn-total').on('input', function() {
                // Update HPP dan harga jual for all rows
                $('.detail-row').each(function() {
                    calculateRowValues($(this));
                });

                // Then calculate overall totals
                calculateTotals();
            });

            // Format harga beli on blur
            $(document).on('blur', '.harga-beli-input', function() {
                const value = $(this).val().replace(/[^\d]/g, '');
                if (value) {
                    $(this).val(formatRupiah(value));
                }
            });

            // Format harga beli on focus
            $(document).on('focus', '.harga-beli-input', function() {
                const value = $(this).val().replace(/[^\d]/g, '');
                $(this).val(value);
            });

            // Add first row on load
            addDetailRow();

            // Functions
            function addDetailRow() {
                console.log('addDetailRow function called');
                // Hide empty row
                $('#empty-row').hide();

                // Get template content and replace index
                let template = $('#detail-row-template').html();
                console.log('Template content:', template ? 'Found' : 'Not found');
                template = template.replace(/__index__/g, detailIndex);

                // Append to tbody
                $('#detail-tbody').append(template);

                // Initialize Select2 for the new row
                $(`.obat-select[data-index="${detailIndex}"]`).select2({
                    placeholder: "Pilih Obat",
                    allowClear: true,
                    width: '100%'
                });

                // Set default date for expired field (1 year from now)
                const defaultDate = new Date();
                defaultDate.setFullYear(defaultDate.getFullYear() + 1);
                const formattedDate = defaultDate.toISOString().split('T')[0];
                $(`input[name="detail[${detailIndex}][tanggal_expired]"]`).val(formattedDate);

                // Update validator with new fields
                updateValidatorWithNewFields(detailIndex);

                // Calculate initial values for the row
                const newRow = $(`#detail-tbody tr:last`);
                calculateRowValues(newRow);

                // Increment index for next row
                detailIndex++;
            }

            function updateValidatorWithNewFields(index) {
                console.log('Adding validation for detail fields at index', index);
                // Nothing to do here - jQuery validate will automatically pick up validation rules
                // based on HTML attributes like "required"
            }

            function calculateRowValues(row) {
                // Get values
                const jumlah = parseInt(row.find('.jumlah-input').val()) || 0;
                const hargaBeli = parseInt(row.find('.harga-beli-input').val().replace(/[^\d]/g, '')) || 0;
                const diskonPersen = parseFloat(row.find('.diskon-persen-input').val()) || 0;
                const marginPersen = parseFloat(row.find('.margin-jual-input').val()) || 0;
                const ppnPersen = parseFloat($('#ppn-total').val()) || 0;

                // Calculate values
                const subtotal = jumlah * hargaBeli;
                const diskonNominal = (diskonPersen / 100) * subtotal;
                const subtotalSetelahDiskon = subtotal - diskonNominal;
                const ppnNominalPerItem = (ppnPersen / 100) * subtotalSetelahDiskon;

                // HPP per unit (termasuk PPN)
                const totalDenganPPN = subtotalSetelahDiskon + ppnNominalPerItem;
                const hppPerUnit = jumlah > 0 ? (subtotalSetelahDiskon + ppnNominalPerItem) / jumlah : 0;

                // Harga jual berdasarkan HPP + margin
                const marginNominal = (marginPersen / 100) * hppPerUnit;
                const hargaJual = hppPerUnit + marginNominal;

                // Update displays
                row.find('.subtotal-item-display').val(formatRupiah(subtotal));
                row.find('.subtotal-item-input').val(subtotal);

                row.find('.diskon-nominal-display').val(formatRupiah(diskonNominal));
                row.find('.diskon-nominal-input').val(diskonNominal);

                // Display HPP per unit
                row.find('.hpp-display').val(formatRupiah(hppPerUnit));
                row.find('.hpp-input').val(hppPerUnit);

                // Display harga jual
                row.find('.harga-jual-display').val(formatRupiah(hargaJual));
                row.find('.harga-jual-input').val(hargaJual);

                // Display total (subtotal - diskon)
                row.find('.total-item-display').val(formatRupiah(subtotalSetelahDiskon));
                row.find('.total-item-input').val(subtotalSetelahDiskon);

                // Calculate overall totals
                calculateTotals();
            }

            function calculateTotals() {
                let subtotal = 0;
                let diskonTotal = 0;
                let subtotalSetelahDiskon = 0;

                // Sum up all rows
                $('.detail-row').each(function() {
                    const rowSubtotal = parseInt($(this).find('.subtotal-item-input').val()) || 0;
                    const rowDiskon = parseInt($(this).find('.diskon-nominal-input').val()) || 0;
                    const rowTotal = parseInt($(this).find('.total-item-input').val()) || 0;

                    subtotal += rowSubtotal;
                    diskonTotal += rowDiskon;
                    subtotalSetelahDiskon += rowTotal;
                });

                // Calculate PPN
                const ppnPersen = parseFloat($('#ppn-total').val()) || 0;
                const ppnNominal = (ppnPersen / 100) * subtotalSetelahDiskon;
                const grandTotal = subtotalSetelahDiskon + ppnNominal;

                // Update displays
                $('#subtotal-display').val(formatRupiah(subtotal));
                $('#subtotal-input').val(subtotal);

                $('#diskon-total-display').val(formatRupiah(diskonTotal));
                $('#diskon-total-input').val(diskonTotal);

                $('#grand-total-display').val(formatRupiah(grandTotal));
                $('#grand-total-input').val(grandTotal);
            }

            function updateEmptyRowVisibility() {
                const hasDetails = $('.detail-row').length > 0;
                if (hasDetails) {
                    $('#empty-row').hide();
                } else {
                    $('#empty-row').show();
                }
            }

            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            function initSelect2() {
                $('select[data-control="select2"]').select2({
                    placeholder: $(this).data('placeholder'),
                    allowClear: true
                });
            }

            function initValidator() {
                $("#form-pembelian").validate({
                    rules: {
                        no_faktur: {
                            required: true,
                            maxlength: 50
                        },
                        tanggal_faktur: {
                            required: true,
                            date: true
                        },
                        supplier_id: {
                            required: true
                        },
                        jenis: {
                            required: true
                        },
                        akun_kas_id: {
                            required: function() {
                                return $('#jenis-pembayaran').val() === 'TUNAI';
                            }
                        },
                        tanggal_jatuh_tempo: {
                            required: function() {
                                return $('#jenis-pembayaran').val() === 'HUTANG';
                            },
                            date: true,
                            greaterThanDate: function() {
                                return $('input[name="tanggal_faktur"]').val();
                            }
                        },
                        ppn_total: {
                            number: true,
                            min: 0,
                            max: 100
                        }
                    },
                    messages: {
                        no_faktur: {
                            required: "No Faktur harus diisi",
                            maxlength: "No Faktur maksimal 50 karakter"
                        },
                        tanggal_faktur: {
                            required: "Tanggal Faktur harus diisi",
                            date: "Format tanggal tidak valid"
                        },
                        supplier_id: {
                            required: "Supplier harus dipilih"
                        },
                        jenis: {
                            required: "Jenis pembayaran harus dipilih"
                        },
                        akun_kas_id: {
                            required: "Akun kas harus dipilih untuk pembayaran tunai"
                        },
                        tanggal_jatuh_tempo: {
                            required: "Tanggal jatuh tempo harus diisi untuk pembayaran hutang",
                            date: "Format tanggal tidak valid",
                            greaterThanDate: "Tanggal jatuh tempo harus setelah tanggal faktur"
                        },
                        ppn_total: {
                            number: "PPN harus berupa angka",
                            min: "PPN minimal 0",
                            max: "PPN maksimal 100"
                        }
                    },
                    errorElement: "div",
                    errorPlacement: function(error, element) {
                        error.addClass("invalid-feedback");

                        if (element.parent(".input-group").length) {
                            error.insertAfter(element.parent());
                        } else if (element.hasClass("select2-hidden-accessible")) {
                            error.insertAfter(element.next("span"));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    highlight: function(element, errorClass, validClass) {
                        $(element).addClass("is-invalid").removeClass("is-valid");

                        // Add animation to the form group
                        $(element).closest('.mb-5').addClass('animate__animated animate__headShake');
                        setTimeout(function() {
                            $('.animate__animated').removeClass(
                                'animate__animated animate__headShake');
                        }, 1000);
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).removeClass("is-invalid").addClass("is-valid");
                    },
                    submitHandler: function(form) {
                        // Check if we have at least one detail row
                        if ($('.detail-row').length === 0) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Minimal satu item harus ditambahkan!',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            return false;
                        }

                        // Show loading state on button
                        var btn = $(form).find('[type="submit"]');
                        var loadingText =
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                        btn.html(loadingText);
                        btn.attr('disabled', true);

                        form.submit();
                    }
                });

                // Add custom validation method for date comparisons
                $.validator.addMethod("greaterThanDate", function(value, element, param) {
                    if (!value || !param) return true;
                    var startDate = new Date(param);
                    var endDate = new Date(value);
                    return endDate >= startDate;
                }, "Tanggal jatuh tempo harus setelah tanggal faktur");
            }
        });
    </script>
@endpush
