@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Tambah Stock Opname</h3>
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
                    <div class="col-md-6">
                        <div class="form-group mb-5">
                            <label class="required form-label">Kode Stock Opname</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                placeholder="Kode Stock Opname" value="{{ $kode }}" readonly />
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
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
                        <div class="form-group mb-5">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                placeholder="Keterangan stock opname">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="separator separator-dashed my-8"></div>

                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-3">
                        <i class="ki-duotone ki-arrows-circle fs-2"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <i class="ki-duotone ki-check fs-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Form validation using jQuery Validation
            $("#formStockOpname").validate({
                rules: {
                    tanggal: {
                        required: true
                    }
                },
                messages: {
                    tanggal: {
                        required: "Tanggal harus diisi"
                    }
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".form-group").append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                },
                submitHandler: function(form) {
                    // Show loading indicator
                    $("#submitButton").attr("data-kt-indicator", "on").prop("disabled", true);

                    // Submit the form
                    form.submit();
                }
            });
        });
    </script>
@endpush
