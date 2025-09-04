@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah Obat</h3>
            <div class="card-toolbar">
                <a href="{{ route('obat.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('obat.store') }}" method="POST" id="obatForm">
                @csrf

                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_obat">Data Obat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_satuan">Satuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_stok">Stok</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Obat -->
                    <div class="tab-pane fade show active" id="tab_obat" role="tabpanel">
                        <div class="row mb-5">
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Kode Obat</label>
                                    <input type="text" class="form-control @error('kode_obat') is-invalid @enderror"
                                        name="kode_obat" value="{{ old('kode_obat', 'OBT' . date('ymd') . '0001') }}"
                                        placeholder="OBTYYMMDDxxxx" />
                                    <span class="form-text text-muted">Dapat diubah atau biarkan kosong untuk pembuatan
                                        otomatis</span>
                                    @error('kode_obat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Nama Obat</label>
                                    <input type="text" class="form-control @error('nama_obat') is-invalid @enderror"
                                        name="nama_obat" value="{{ old('nama_obat') }}" required />
                                    @error('nama_obat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Pabrik</label>
                                    <select name="pabrik_id" class="form-select @error('pabrik_id') is-invalid @enderror"
                                        required>
                                        <option value="">-- Pilih Pabrik --</option>
                                        @foreach ($pabriks as $pabrik)
                                            <option value="{{ $pabrik->id }}"
                                                {{ old('pabrik_id') == $pabrik->id ? 'selected' : '' }}>
                                                {{ $pabrik->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pabrik_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Golongan</label>
                                    <select name="golongan_id"
                                        class="form-select @error('golongan_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Golongan --</option>
                                        @foreach ($golongans as $golongan)
                                            <option value="{{ $golongan->id }}"
                                                {{ old('golongan_id') == $golongan->id ? 'selected' : '' }}>
                                                {{ $golongan->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('golongan_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Kategori</label>
                                    <select name="kategori_id"
                                        class="form-select @error('kategori_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach ($kategoris as $kategori)
                                            <option value="{{ $kategori->id }}"
                                                {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                                {{ $kategori->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kategori_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Jenis Obat</label>
                                    <input type="text" class="form-control @error('jenis_obat') is-invalid @enderror"
                                        name="jenis_obat" value="{{ old('jenis_obat') }}" required />
                                    @error('jenis_obat')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Minimal Stok</label>
                                    <input type="number" class="form-control @error('minimal_stok') is-invalid @enderror"
                                        name="minimal_stok" value="{{ old('minimal_stok', 0) }}" required min="0" />
                                    @error('minimal_stok')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label required">Status</label>
                                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror"
                                        required>
                                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Non Aktif
                                        </option>
                                    </select>
                                    @error('is_active')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Kemasan</label>
                                    <input type="text" class="form-control @error('kemasan') is-invalid @enderror"
                                        name="kemasan" value="{{ old('kemasan') }}" />
                                    @error('kemasan')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-5">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Indikasi</label>
                                    <textarea name="indikasi" class="form-control @error('indikasi') is-invalid @enderror" rows="3">{{ old('indikasi') }}</textarea>
                                    @error('indikasi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Kandungan</label>
                                    <textarea name="kandungan" class="form-control @error('kandungan') is-invalid @enderror" rows="3">{{ old('kandungan') }}</textarea>
                                    @error('kandungan')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Dosis</label>
                                    <textarea name="dosis" class="form-control @error('dosis') is-invalid @enderror" rows="3">{{ old('dosis') }}</textarea>
                                    @error('dosis')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Efek Samping</label>
                                    <textarea name="efek_samping" class="form-control @error('efek_samping') is-invalid @enderror" rows="3">{{ old('efek_samping') }}</textarea>
                                    @error('efek_samping')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Zat Aktif/Prekursor</label>
                                    <textarea name="zat_aktif_prekursor" class="form-control @error('zat_aktif_prekursor') is-invalid @enderror"
                                        rows="3">{{ old('zat_aktif_prekursor') }}</textarea>
                                    @error('zat_aktif_prekursor')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5">
                                    <label class="form-label">Aturan Pakai</label>
                                    <textarea name="aturan_pakai" class="form-control @error('aturan_pakai') is-invalid @enderror" rows="3">{{ old('aturan_pakai') }}</textarea>
                                    @error('aturan_pakai')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Satuan -->
                    <div class="tab-pane fade" id="tab_satuan" role="tabpanel">
                        <div class="alert alert-primary d-flex align-items-center p-5 mb-5">
                            <span class="svg-icon svg-icon-2hx svg-icon-primary me-3">
                                <i class="ki-duotone ki-information-5 fs-1"></i>
                            </span>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-primary">Informasi Satuan</h4>
                                <span>Tambahkan satuan obat beserta harga belinya. Data satuan wajib ditambahkan untuk bisa
                                    menambahkan stok.</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-5">
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddSatuan">
                                <i class="ki-duotone ki-plus fs-2"></i>Tambah Satuan
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle" id="satuanTable">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>Satuan</th>
                                        <th>Harga Beli</th>
                                        <th>Diskon (%)</th>
                                        <th>Profit (%)</th>
                                        <th>Harga Jual</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic content will be here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Stok -->
                    <div class="tab-pane fade" id="tab_stok" role="tabpanel">
                        <div class="alert alert-primary d-flex align-items-center p-5 mb-5">
                            <span class="svg-icon svg-icon-2hx svg-icon-primary me-3">
                                <i class="ki-duotone ki-information-5 fs-1"></i>
                            </span>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-primary">Informasi Stok</h4>
                                <span>Stok hanya dapat ditambahkan setelah satuan obat diisi. Silakan isi data satuan
                                    terlebih dahulu.</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-5">
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddStok" disabled>
                                <i class="ki-duotone ki-plus fs-2"></i>Tambah Stok
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle" id="stokTable">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>Satuan</th>
                                        <th>Lokasi</th>
                                        <th>No. Batch</th>
                                        <th>Expired</th>
                                        <th>Qty</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic content will be here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="separator my-10"></div>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-light me-3" onclick="window.history.back();">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Add Satuan -->
    <div class="modal fade" id="modalAddSatuan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Satuan Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAddSatuan">
                        <div class="mb-5">
                            <label class="form-label required">Satuan</label>
                            <select name="satuan_id" class="form-select" required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach ($satuans as $satuan)
                                    <option value="{{ $satuan->id }}">{{ $satuan->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Harga Beli</label>
                            <input type="text" name="harga_beli" class="form-control input-currency" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Diskon (%)</label>
                            <input type="text" name="diskon_persen" class="form-control" value="0" />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Persentase Keuntungan (%)</label>
                            <input type="text" name="profit_persen" class="form-control" value="10" />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Harga Jual</label>
                            <input type="text" name="harga_jual" class="form-control input-currency" required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveSatuan">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Stok -->
    <div class="modal fade" id="modalAddStok" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Stok Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAddStok">
                        <div class="mb-5">
                            <label class="form-label required">Satuan</label>
                            <select name="satuan_id" class="form-select" required>
                                <option value="">-- Pilih Satuan --</option>
                                <!-- Will be filled dynamically -->
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Lokasi</label>
                            <select name="lokasi_id" class="form-select" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach ($lokasis as $lokasi)
                                    <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">No. Batch</label>
                            <input type="text" name="no_batch" class="form-control" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Tanggal Expired</label>
                            <input type="date" name="tanggal_expired" class="form-control" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-5">
                            <label class="form-label required">Qty</label>
                            <input type="number" name="qty" class="form-control" min="1" value="1"
                                required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSaveStok">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Array to store satuans
        let satuans = [];

        // Array to store stocks
        let stocks = [];

        $(document).ready(function() {
            // Format kode obat
            $('input[name="kode_obat"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9a-zA-Z]/g, '');
                if (!value.toUpperCase().startsWith('OBT')) {
                    value = 'OBT' + value;
                } else {
                    // Ensure OBT in uppercase
                    if (value.startsWith('obt') || value.startsWith('Obt')) {
                        value = 'OBT' + value.substring(3);
                    }
                }
                $(this).val(value);
            });

            // Format currency inputs
            $('.input-currency').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value) {
                    value = parseInt(value).toLocaleString('id-ID');
                }
                $(this).val(value);

                // If this is the purchase price, automatically calculate selling price
                if ($(this).attr('name') === 'harga_beli') {
                    calculateSellingPrice();
                }
            });

            // Calculate selling price when profit percentage changes
            $('#formAddSatuan input[name="profit_persen"]').on('input', function() {
                calculateSellingPrice();
            });

            // Open modal to add satuan
            $('#btnAddSatuan').on('click', function() {
                // Reset form
                $('#formAddSatuan')[0].reset();
                $('#formAddSatuan select[name="satuan_id"]').val('');
                $('#formAddSatuan .is-invalid').removeClass('is-invalid');
                $('#modalAddSatuan').modal('show');
            });

            // Save satuan
            $('#btnSaveSatuan').on('click', function() {
                // Get form data
                const satuanId = $('#formAddSatuan select[name="satuan_id"]').val();
                const hargaBeli = $('#formAddSatuan input[name="harga_beli"]').val().replace(/\D/g, '');
                const diskonPersen = $('#formAddSatuan input[name="diskon_persen"]').val();
                const profitPersen = $('#formAddSatuan input[name="profit_persen"]').val();
                const hargaJual = $('#formAddSatuan input[name="harga_jual"]').val().replace(/\D/g, '');

                // Validate form
                let isValid = true;

                if (!satuanId) {
                    $('#formAddSatuan select[name="satuan_id"]').addClass('is-invalid');
                    $('#formAddSatuan select[name="satuan_id"]').next('.invalid-feedback').text(
                        'Pilih satuan');
                    isValid = false;
                } else {
                    $('#formAddSatuan select[name="satuan_id"]').removeClass('is-invalid');
                }

                if (!hargaBeli) {
                    $('#formAddSatuan input[name="harga_beli"]').addClass('is-invalid');
                    $('#formAddSatuan input[name="harga_beli"]').next('.invalid-feedback').text(
                        'Harga beli harus diisi');
                    isValid = false;
                } else {
                    $('#formAddSatuan input[name="harga_beli"]').removeClass('is-invalid');
                }

                if (!hargaJual) {
                    $('#formAddSatuan input[name="harga_jual"]').addClass('is-invalid');
                    $('#formAddSatuan input[name="harga_jual"]').next('.invalid-feedback').text(
                        'Harga jual harus diisi');
                    isValid = false;
                } else {
                    $('#formAddSatuan input[name="harga_jual"]').removeClass('is-invalid');
                }

                if (!isValid) {
                    return;
                }

                // Check if satuan already exists
                if (satuans.some(item => item.satuan_id === satuanId)) {
                    $('#formAddSatuan select[name="satuan_id"]').addClass('is-invalid');
                    $('#formAddSatuan select[name="satuan_id"]').next('.invalid-feedback').text(
                        'Satuan sudah ditambahkan');
                    return;
                }

                // Get satuan name
                const satuanText = $('#formAddSatuan select[name="satuan_id"] option:selected').text();

                // Add to satuans array
                satuans.push({
                    satuan_id: satuanId,
                    satuan_text: satuanText,
                    harga_beli: hargaBeli,
                    diskon_persen: diskonPersen || 0,
                    profit_persen: profitPersen || 10,
                    harga_jual: hargaJual,
                });

                // Add row to table
                const newRow = `
                    <tr id="satuan-row-${satuanId}">
                        <td>${satuanText}</td>
                        <td>${parseInt(hargaBeli).toLocaleString('id-ID')}</td>
                        <td>${diskonPersen || 0}%</td>
                        <td>${profitPersen || 10}%</td>
                        <td>${parseInt(hargaJual).toLocaleString('id-ID')}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-satuan" data-satuan-id="${satuanId}">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
                $('#satuanTable tbody').append(newRow);

                // Update hidden input for satuan data
                updateHiddenInputs();

                // Enable add stock button if at least one satuan is added
                if (satuans.length > 0) {
                    $('#btnAddStok').prop('disabled', false);
                    // Update satuan options in stok modal
                    updateStokSatuanOptions();
                }

                // Close modal
                $('#modalAddSatuan').modal('hide');
            });

            // Delete satuan
            $(document).on('click', '.btn-delete-satuan', function() {
                const satuanId = $(this).data('satuan-id');

                // Check if any stock uses this satuan
                const stockExists = stocks.some(item => item.satuan_id === satuanId);
                if (stockExists) {
                    alert('Tidak dapat menghapus satuan karena masih memiliki stok');
                    return;
                }

                // Remove from array
                satuans = satuans.filter(item => item.satuan_id !== satuanId);

                // Remove row
                $(`#satuan-row-${satuanId}`).remove();

                // Update hidden input for satuan data
                updateHiddenInputs();

                // Disable add stock button if no satuan is left
                if (satuans.length === 0) {
                    $('#btnAddStok').prop('disabled', true);
                }
            });

            // Open modal to add stok
            $('#btnAddStok').on('click', function() {
                // Reset form
                $('#formAddStok')[0].reset();
                $('#formAddStok select[name="satuan_id"]').val('');
                $('#formAddStok select[name="lokasi_id"]').val('');
                $('#formAddStok .is-invalid').removeClass('is-invalid');

                // Set min date for expired to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];
                $('#formAddStok input[name="tanggal_expired"]').attr('min', tomorrowStr);

                $('#modalAddStok').modal('show');
            });

            // Save stok
            $('#btnSaveStok').on('click', function() {
                // Get form data
                const satuanId = $('#formAddStok select[name="satuan_id"]').val();
                const lokasiId = $('#formAddStok select[name="lokasi_id"]').val();
                const noBatch = $('#formAddStok input[name="no_batch"]').val();
                const tanggalExpired = $('#formAddStok input[name="tanggal_expired"]').val();
                const qty = $('#formAddStok input[name="qty"]').val();

                // Validate form
                let isValid = true;

                if (!satuanId) {
                    $('#formAddStok select[name="satuan_id"]').addClass('is-invalid');
                    $('#formAddStok select[name="satuan_id"]').next('.invalid-feedback').text(
                        'Pilih satuan');
                    isValid = false;
                } else {
                    $('#formAddStok select[name="satuan_id"]').removeClass('is-invalid');
                }

                if (!lokasiId) {
                    $('#formAddStok select[name="lokasi_id"]').addClass('is-invalid');
                    $('#formAddStok select[name="lokasi_id"]').next('.invalid-feedback').text(
                        'Pilih lokasi');
                    isValid = false;
                } else {
                    $('#formAddStok select[name="lokasi_id"]').removeClass('is-invalid');
                }

                if (!noBatch) {
                    $('#formAddStok input[name="no_batch"]').addClass('is-invalid');
                    $('#formAddStok input[name="no_batch"]').next('.invalid-feedback').text(
                        'No batch harus diisi');
                    isValid = false;
                } else {
                    $('#formAddStok input[name="no_batch"]').removeClass('is-invalid');
                }

                if (!tanggalExpired) {
                    $('#formAddStok input[name="tanggal_expired"]').addClass('is-invalid');
                    $('#formAddStok input[name="tanggal_expired"]').next('.invalid-feedback').text(
                        'Tanggal expired harus diisi');
                    isValid = false;
                } else {
                    $('#formAddStok input[name="tanggal_expired"]').removeClass('is-invalid');
                }

                if (!qty || qty < 1) {
                    $('#formAddStok input[name="qty"]').addClass('is-invalid');
                    $('#formAddStok input[name="qty"]').next('.invalid-feedback').text('Qty minimal 1');
                    isValid = false;
                } else {
                    $('#formAddStok input[name="qty"]').removeClass('is-invalid');
                }

                if (!isValid) {
                    return;
                }

                // Get satuan and lokasi names
                const satuanText = $('#formAddStok select[name="satuan_id"] option:selected').text();
                const lokasiText = $('#formAddStok select[name="lokasi_id"] option:selected').text();

                // Generate unique ID for this stock
                const stockId = 'new-' + Date.now();

                // Add to stocks array
                stocks.push({
                    id: stockId,
                    satuan_id: satuanId,
                    lokasi_id: lokasiId,
                    no_batch: noBatch,
                    tanggal_expired: tanggalExpired,
                    qty: qty,
                });

                // Format date for display
                const expiredDate = new Date(tanggalExpired);
                const formattedDate = expiredDate.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });

                // Add row to table
                const newRow = `
                    <tr id="stock-row-${stockId}">
                        <td>${satuanText}</td>
                        <td>${lokasiText}</td>
                        <td>${noBatch}</td>
                        <td>${formattedDate}</td>
                        <td>${qty}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-stock" data-stock-id="${stockId}">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
                $('#stokTable tbody').append(newRow);

                // Update hidden input for stock data
                updateHiddenInputs();

                // Close modal
                $('#modalAddStok').modal('hide');
            });

            // Delete stock
            $(document).on('click', '.btn-delete-stock', function() {
                const stockId = $(this).data('stock-id');

                // Remove from array
                stocks = stocks.filter(item => item.id !== stockId);

                // Remove row
                $(`#stock-row-${stockId}`).remove();

                // Update hidden input for stock data
                updateHiddenInputs();
            });

            // Form validation
            $("#obatForm").validate({
                rules: {
                    kode_obat: {
                        maxlength: 20,
                        minlength: 4
                    },
                    nama_obat: {
                        required: true,
                        maxlength: 255,
                        minlength: 3
                    },
                    pabrik_id: {
                        required: true
                    },
                    golongan_id: {
                        required: true
                    },
                    kategori_id: {
                        required: true
                    },
                    jenis_obat: {
                        required: true
                    },
                    minimal_stok: {
                        required: true,
                        min: 0
                    },
                    is_active: {
                        required: true
                    }
                },
                messages: {
                    kode_obat: {
                        maxlength: "Kode obat maksimal 20 karakter",
                        minlength: "Kode obat minimal 4 karakter"
                    },
                    nama_obat: {
                        required: "Nama obat harus diisi",
                        maxlength: "Nama obat maksimal 255 karakter",
                        minlength: "Nama obat minimal 3 karakter"
                    },
                    pabrik_id: {
                        required: "Pabrik harus dipilih"
                    },
                    golongan_id: {
                        required: "Golongan obat harus dipilih"
                    },
                    kategori_id: {
                        required: "Kategori obat harus dipilih"
                    },
                    jenis_obat: {
                        required: "Jenis obat harus diisi"
                    },
                    minimal_stok: {
                        required: "Minimal stok harus diisi",
                        min: "Minimal stok minimal 0"
                    },
                    is_active: {
                        required: "Status harus dipilih"
                    }
                },
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");

                    if (element.parent(".input-group").length) {
                        error.insertAfter(element.parent());
                    } else if (element.hasClass("form-select")) {
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
                    // Show loading state on button
                    var btn = $(form).find('[type="submit"]');
                    var loadingText =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                    btn.html(loadingText);
                    btn.attr('disabled', true);

                    form.submit();
                }
            });
        });

        // Helper functions
        function updateHiddenInputs() {
            // Remove existing hidden inputs
            $('input[name^="satuan["]').remove();
            $('input[name^="stok["]').remove();

            // Add hidden inputs for satuans
            satuans.forEach((satuan, index) => {
                const prefix = `satuan[${index}]`;
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[satuan_id]" value="${satuan.satuan_id}">`);
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[harga_beli]" value="${satuan.harga_beli}">`);
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[diskon_persen]" value="${satuan.diskon_persen}">`);
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[profit_persen]" value="${satuan.profit_persen || 10}">`
                    );
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[harga_jual]" value="${satuan.harga_jual}">`);
            });

            // Add hidden inputs for stocks
            stocks.forEach((stok, index) => {
                const prefix = `stok[${index}]`;
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[satuan_id]" value="${stok.satuan_id}">`);
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[lokasi_id]" value="${stok.lokasi_id}">`);
                $('#obatForm').append(`<input type="hidden" name="${prefix}[no_batch]" value="${stok.no_batch}">`);
                $('#obatForm').append(
                    `<input type="hidden" name="${prefix}[tanggal_expired]" value="${stok.tanggal_expired}">`);
                $('#obatForm').append(`<input type="hidden" name="${prefix}[qty]" value="${stok.qty}">`);
            });
        }

        function updateStokSatuanOptions() {
            // Clear current options except the first one
            const $select = $('#formAddStok select[name="satuan_id"]');
            $select.find('option:not(:first)').remove();

            // Add options for each satuan
            satuans.forEach(satuan => {
                $select.append(`<option value="${satuan.satuan_id}">${satuan.satuan_text}</option>`);
            });
        }

        // Function to calculate selling price based on purchase price and profit percentage
        function calculateSellingPrice() {
            const hargaBeliInput = $('#formAddSatuan input[name="harga_beli"]');
            const profitPersenInput = $('#formAddSatuan input[name="profit_persen"]');
            const hargaJualInput = $('#formAddSatuan input[name="harga_jual"]');

            const hargaBeliStr = hargaBeliInput.val().replace(/\D/g, '');
            const profitPersen = parseFloat(profitPersenInput.val()) || 0;

            if (hargaBeliStr && !isNaN(profitPersen)) {
                const hargaBeli = parseInt(hargaBeliStr);
                const profitAmount = hargaBeli * (profitPersen / 100);
                const hargaJual = Math.round(hargaBeli + profitAmount);

                // Format and set the calculated selling price
                hargaJualInput.val(hargaJual.toLocaleString('id-ID'));
            }
        }
    </script>
@endpush
