@extends('layout.app')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <h3>Tambah Retur Pembelian</h3>
        </div>
    </div>
    <div class="card-body py-4">
        <form id="form-retur" action="{{ route('retur_pembelian.store') }}" method="POST">
            @csrf
            
            <!-- Search Pembelian -->
            <div class="row mb-6">
                <div class="col-md-6">
                    <div class="mb-5">
                        <label class="form-label required">No Faktur Pembelian</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-no-faktur" placeholder="Masukkan No Faktur" required>
                            <button type="button" class="btn btn-primary" id="search-pembelian-btn">
                                <i class="ki-duotone ki-magnifier fs-2"></i>Cari
                            </button>
                        </div>
                        <div class="form-text">Masukkan nomor faktur pembelian yang ingin diretur</div>
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
                            <input type="date" class="form-control @error('tanggal_retur') is-invalid @enderror" name="tanggal_retur" value="{{ old('tanggal_retur', date('Y-m-d')) }}" required>
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
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Search for pembelian
        $('#search-pembelian-btn').on('click', function() {
            const noFaktur = $('#search-no-faktur').val();
            
            if (!noFaktur) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Nomor faktur harus diisi!',
                    icon: 'error',
                    confirmButtonText: 'Ok'
                });
                return;
            }
            
            // Show loading
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mencari...');
            $(this).prop('disabled', true);
            
            $.ajax({
                url: "{{ route('retur_pembelian.search') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    no_faktur: noFaktur
                },
                success: function(response) {
                    // Reset button
                    $('#search-pembelian-btn').html('<i class="ki-duotone ki-magnifier fs-2"></i>Cari');
                    $('#search-pembelian-btn').prop('disabled', false);
                    
                    if (!response.success) {
                        Swal.fire({
                            title: 'Tidak ditemukan!',
                            text: response.message,
                            icon: 'warning',
                            confirmButtonText: 'Ok'
                        });
                        return;
                    }
                    
                    // Populate pembelian details
                    const pembelian = response.pembelian;
                    
                    $('#pembelian-id').val(pembelian.id);
                    $('#no-faktur').val(pembelian.no_faktur);
                    $('#tanggal-faktur').val(new Date(pembelian.tanggal_faktur).toLocaleDateString('id-ID'));
                    $('#supplier-nama').val(pembelian.supplier ? pembelian.supplier.nama : '-');
                    
                    // Populate detail items
                    populateDetailItems(pembelian.details);
                    
                    // Show pembelian details section
                    $('#pembelian-details').show();
                },
                error: function(xhr) {
                    // Reset button
                    $('#search-pembelian-btn').html('<i class="ki-duotone ki-magnifier fs-2"></i>Cari');
                    $('#search-pembelian-btn').prop('disabled', false);
                    
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat mencari data pembelian',
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
            submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
            submitBtn.attr('disabled', true);
            
            return true;
        });
        
        // Function to populate detail items
        function populateDetailItems(details) {
            const tbody = $('#detail-tbody');
            tbody.empty();
            
            if (details.length === 0) {
                tbody.append('<tr><td colspan="10" class="text-center text-muted">Tidak ada item yang dapat diretur</td></tr>');
                return;
            }
            
            details.forEach(function(detail, index) {
                if (detail.remaining_qty > 0) {
                    // Calculate returned quantity
                    const returQty = detail.jumlah - detail.remaining_qty;
                    
                    const row = `
                        <tr data-detail-id="${detail.id}" data-max-qty="${detail.remaining_qty}" data-harga-beli="${detail.harga_beli}">
                            <input type="hidden" name="detail[${index}][pembelian_detail_id]" value="${detail.id}">
                            <input type="hidden" name="detail[${index}][obat_id]" value="${detail.obat_id}">
                            <input type="hidden" name="detail[${index}][satuan_id]" value="${detail.satuan_id}">
                            <input type="hidden" name="detail[${index}][no_batch]" value="${detail.no_batch}">
                            <input type="hidden" name="detail[${index}][lokasi_id]" value="${detail.stok[0] ? detail.stok[0].lokasi_id : ''}">
                            <td>${detail.obat.nama_obat}</td>
                            <td>${detail.satuan.nama}</td>
                            <td>${detail.no_batch}</td>
                            <td>${new Date(detail.tanggal_expired).toLocaleDateString('id-ID')}</td>
                            <td>${detail.stok[0] ? detail.stok[0].lokasi.nama : '-'}</td>
                            <td>${detail.jumlah}</td>
                            <td>${returQty}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm jumlah-retur-input" 
                                    name="detail[${index}][jumlah]" min="0" max="${detail.remaining_qty}" value="0">
                            </td>
                            <td>Rp ${formatRupiah(detail.harga_beli)}</td>
                            <td class="total-display">Rp 0</td>
                        </tr>
                    `;
                    
                    tbody.append(row);
                }
            });
        }
        
        // Function to format numbers as rupiah
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }
    });
</script>
@endpush
