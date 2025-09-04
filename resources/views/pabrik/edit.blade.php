@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Pabrik</h3>
            <div class="card-toolbar">
                <a href="{{ route('pabrik.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('pabrik.update', $pabrik->id) }}" method="POST" id="pabrikForm">
                @csrf
                @method('PUT')
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" name="kode"
                                value="{{ old('kode', $pabrik->kode) }}" />
                            <span class="form-text text-muted">Kode akan otomatis diawali dengan 'PAB'</span>
                            @error('kode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama"
                                value="{{ old('nama', $pabrik->nama) }}" required />
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $pabrik->status) == '1' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="0" {{ old('status', $pabrik->status) == '0' ? 'selected' : '' }}>Non
                                    Aktif</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-5">
                            <label class="form-label required">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat', $pabrik->alamat) }}</textarea>
                            @error('alamat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-light me-3" onclick="window.history.back();">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Format kode pabrik
            $('input[name="kode"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9a-zA-Z]/g, '');
                if (!value.toUpperCase().startsWith('PAB')) {
                    value = 'PAB' + value;
                } else {
                    // Pastikan PAB dalam huruf besar
                    if (value.startsWith('pab') || value.startsWith('Pab')) {
                        value = 'PAB' + value.substring(3);
                    }
                }
                $(this).val(value);
            });

            // Client-side validation with Bootstrap styling
            $("#pabrikForm").validate({
                rules: {
                    kode: {
                        required: true,
                        maxlength: 16,
                        minlength: 4
                    },
                    nama: {
                        required: true,
                        maxlength: 100,
                        minlength: 3
                    },
                    alamat: {
                        required: true,
                        minlength: 5
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    kode: {
                        required: "Kode pabrik harus diisi",
                        maxlength: "Kode pabrik maksimal 16 karakter",
                        minlength: "Kode pabrik minimal 4 karakter"
                    },
                    nama: {
                        required: "Nama pabrik harus diisi",
                        maxlength: "Nama pabrik maksimal 100 karakter",
                        minlength: "Nama pabrik minimal 3 karakter"
                    },
                    alamat: {
                        required: "Alamat harus diisi",
                        minlength: "Alamat minimal 5 karakter"
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
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memperbarui...';
                    btn.html(loadingText);
                    btn.attr('disabled', true);

                    form.submit();
                }
            });
        });
    </script>
@endpush
