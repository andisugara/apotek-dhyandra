@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Buat Stock Opname</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.index') }}" class="btn btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('stock_opname.store') }}" method="POST" id="formStockOpname">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-4">
                        <div class="form-group mb-5">
                            <label class="required form-label">Kode Stock Opname</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                placeholder="Kode stock opname" value="{{ old('kode', $kode) }}" readonly />
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-5">
                            <label class="required form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', date('Y-m-d')) }}" />
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                placeholder="Keterangan stock opname (opsional)">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="ki-duotone ki-save-2 fs-2"></i>Simpan & Lanjutkan
                        <span class="indicator-progress">Menyimpan...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Form validation
            $("#formStockOpname").validate({
                rules: {
                    kode: {
                        required: true
                    },
                    tanggal: {
                        required: true,
                        date: true
                    }
                },
                messages: {
                    kode: {
                        required: "Kode stock opname harus diisi"
                    },
                    tanggal: {
                        required: "Tanggal harus diisi",
                        date: "Format tanggal tidak valid"
                    }
                },
                submitHandler: function(form) {
                    // Show loading indicator
                    $('#btnSimpan').attr('data-kt-indicator', 'on').prop('disabled', true);
                    form.submit();
                }
            });
        });
    </script>
@endpush
