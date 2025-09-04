@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah Akun</h3>
            <div class="card-toolbar">
                <a href="{{ route('akun.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('akun.store') }}" method="POST" id="akunForm">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" name="kode"
                                value="{{ old('kode', 'AKN' . date('ymd') . '0001') }}" placeholder="AKNYYMMDDxxxx" />
                            <span class="form-text text-muted">Dapat diubah atau biarkan kosong untuk pembuatan
                                otomatis</span>
                            @error('kode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama"
                                value="{{ old('nama') }}" required />
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-light me-3" onclick="window.history.back();">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Format kode akun
            $('input[name="kode"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9a-zA-Z]/g, '');
                if (!value.toUpperCase().startsWith('AKN')) {
                    value = 'AKN' + value;
                } else {
                    // Pastikan AKN dalam huruf besar
                    if (value.startsWith('akn') || value.startsWith('Akn')) {
                        value = 'AKN' + value.substring(3);
                    }
                }
                $(this).val(value);
            });

            // Client-side validation with Bootstrap styling
            $("#akunForm").validate({
                rules: {
                    kode: {
                        maxlength: 16,
                        minlength: 4
                    },
                    nama: {
                        required: true,
                        maxlength: 100,
                        minlength: 3
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    kode: {
                        maxlength: "Kode akun maksimal 16 karakter",
                        minlength: "Kode akun minimal 4 karakter"
                    },
                    nama: {
                        required: "Nama akun harus diisi",
                        maxlength: "Nama akun maksimal 100 karakter",
                        minlength: "Nama akun minimal 3 karakter"
                    },
                    status: {
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
    </script>
@endpush
