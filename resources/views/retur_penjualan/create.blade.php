@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Tambah Retur Penjualan</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('retur_penjualan.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="form-retur" action="{{ route('retur_penjualan.store') }}" method="POST">
                @csrf

                <div class="row mb-10">
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="form-label required">Tanggal Retur</label>
                            <input type="date" class="form-control" name="tanggal_retur" required
                                value="{{ old('tanggal_retur', now()->toDateString()) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="form-label required">No. Faktur</label>
                            <select class="form-select" name="penjualan_id" id="penjualanSelect" required>
                                <option value="">Pilih No. Faktur</option>
                                @if (old('penjualan_id'))
                                    <option value="{{ old('penjualan_id') }}" selected>
                                        {{ old('no_faktur', 'No. Faktur Tersimpan') }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-5">
                            <label class="form-label">Nama Pasien</label>
                            <input type="text" class="form-control" id="pasienNama" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mb-10">
                    <div class="col-md-12">
                        <div class="mb-5">
                            <label class="form-label required">Alasan Retur</label>
                            <textarea class="form-control" name="alasan" rows="3" required>{{ old('alasan') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="separator my-10"></div>

                <div class="row mb-5">
                    <div class="col">
                        <h4>Detail Retur</h4>
                        <div class="alert alert-info">
                            Pilih No. Faktur terlebih dahulu untuk menampilkan item penjualan.
                        </div>
                        <div id="penjualanDetail" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-row-bordered table-row-dashed gy-4" id="detailTable">
                                    <thead>
                                        <tr class="fw-bold fs-6 text-gray-800">
                                            <th>Obat</th>
                                            <th>Satuan</th>
                                            <th>Jumlah Beli</th>
                                            <th>Jumlah Retur</th>
                                            <th>Harga</th>
                                            <th>Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Detail will be populated dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <!-- Empty column for spacing -->
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column gap-3 mt-5">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold fs-6">Subtotal:</span>
                                <span class="fs-6" id="subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold fs-6">Diskon:</span>
                                <span class="fs-6" id="diskon">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold fs-6">PPN:</span>
                                <span class="fs-6" id="ppn">Rp 0</span>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-6">Grand Total:</span>
                                <span class="fw-bold fs-6" id="grandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('retur_penjualan.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
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
            // Initialize Select2 for penjualan select
            $('#penjualanSelect').select2({
                placeholder: 'Cari No. Faktur',
                allowClear: true,
                ajax: {
                    url: "{{ route('retur_penjualan.search_select2') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.data,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: formatPenjualan,
                templateSelection: formatPenjualanSelection
            });

            // Function to format penjualan results
            function formatPenjualan(penjualan) {
                if (penjualan.loading) return penjualan.text;
                if (!penjualan.id) return penjualan.text;

                var $container = $(
                    `<div class='select2-result-penjualan'>
                        <div class='select2-result-penjualan__title'>No. Faktur: ${penjualan.no_faktur}</div>
                        <div class='select2-result-penjualan__meta'>
                            ${penjualan.pasien ? 'Pasien: ' + penjualan.pasien.nama : ''}
                        </div>
                    </div>`
                );

                return $container;
            }

            // Function to format penjualan selection
            function formatPenjualanSelection(penjualan) {
                return penjualan.no_faktur || penjualan.text;
            }

            // Handle penjualan selection
            $('#penjualanSelect').on('select2:select', function(e) {
                var data = e.params.data;
                if (data.id) {
                    loadPenjualanDetail(data.id);
                    if (data.pasien && data.pasien.nama) {
                        $('#pasienNama').val(data.pasien.nama);
                    } else {
                        $('#pasienNama').val('-');
                    }
                }
            });

            // Handle penjualan clear
            $('#penjualanSelect').on('select2:clear', function() {
                $('#penjualanDetail').addClass('d-none');
                $('#detailTable tbody').html('');
                $('#pasienNama').val('');
                updateTotals();
            });

            // Load penjualan details
            function loadPenjualanDetail(penjualanId) {
                $.ajax({
                    url: "{{ route('retur_penjualan.search_by_id') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        penjualan_id: penjualanId
                    },
                    success: function(response) {
                        if (response.success) {
                            displayPenjualanDetail(response.penjualan);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat memuat detail penjualan.'
                        });
                    }
                });
            }

            // Display penjualan details
            function displayPenjualanDetail(penjualan) {
                var tableBody = $('#detailTable tbody');
                tableBody.html(''); // Clear existing rows

                if (!penjualan.details || penjualan.details.length === 0) {
                    $('#penjualanDetail').addClass('d-none');
                    return;
                }

                var index = 0;
                penjualan.details.forEach(function(detail) {
                    if (!detail.remaining_qty || detail.remaining_qty <= 0) {
                        // Skip items that can't be returned
                        return;
                    }

                    var row = `
                        <tr>
                            <td>
                                ${detail.obat ? detail.obat.nama_obat : 'Obat tidak ditemukan'}
                                <input type="hidden" name="detail[${index}][penjualan_detail_id]" value="${detail.id}">
                                <input type="hidden" name="detail[${index}][obat_id]" value="${detail.obat_id}">
                                <input type="hidden" name="detail[${index}][satuan_id]" value="${detail.satuan_id}">
                                <input type="hidden" name="detail[${index}][harga]" value="${detail.harga}">
                            </td>
                            <td>${detail.satuan ? detail.satuan.nama : 'Satuan tidak ditemukan'}</td>
                            <td>${detail.jumlah}</td>
                            <td>
                                <input type="number" class="form-control qty-input"
                                    name="detail[${index}][jumlah]"
                                    min="0" max="${detail.remaining_qty}"
                                    value="0"
                                    data-price="${detail.harga}"
                                    data-discount="${detail.diskon / detail.subtotal || 0}"
                                    data-index="${index}">
                                <div class="form-text">Max: ${detail.remaining_qty}</div>
                            </td>
                            <td>Rp ${formatNumber(detail.harga)}</td>
                            <td>
                                <select class="form-select" name="detail[${index}][lokasi_id]" required>
                                    @foreach ($lokasis as $lokasi)
                                        <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                    index++;
                });

                $('#penjualanDetail').removeClass('d-none');

                // Initialize change event for quantity inputs
                $('.qty-input').on('change', function() {
                    updateTotals();
                });

                updateTotals();
            }

            // Format number with thousand separator
            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            // Update totals
            function updateTotals() {
                var subtotal = 0;
                var diskonTotal = 0;
                var ppnTotal = 0;

                $('.qty-input').each(function() {
                    var qty = parseInt($(this).val()) || 0;
                    var price = parseFloat($(this).data('price')) || 0;
                    var discountRate = parseFloat($(this).data('discount')) || 0;

                    var itemSubtotal = qty * price;
                    var itemDiscount = itemSubtotal * discountRate;

                    subtotal += itemSubtotal;
                    diskonTotal += itemDiscount;
                });

                // Calculate PPN as 11% of (subtotal - diskonTotal)
                ppnTotal = (subtotal - diskonTotal) * 0.11;
                var grandTotal = subtotal - diskonTotal + ppnTotal;

                // Update display
                $('#subtotal').text('Rp ' + formatNumber(subtotal));
                $('#diskon').text('Rp ' + formatNumber(diskonTotal));
                $('#ppn').text('Rp ' + formatNumber(ppnTotal));
                $('#grandTotal').text('Rp ' + formatNumber(grandTotal));
            }

            // Form validation before submit
            $('#form-retur').on('submit', function(e) {
                var totalQty = 0;
                $('.qty-input').each(function() {
                    totalQty += parseInt($(this).val()) || 0;
                });

                if (totalQty === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Minimal satu item harus memiliki jumlah retur lebih dari 0'
                    });
                    return false;
                }

                return true;
            });
        });
    </script>
@endpush
