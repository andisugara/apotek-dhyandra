@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Supplier</h3>
            <div class="card-toolbar">
                <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('supplier.update', $supplier->id) }}" method="POST" id="supplierForm">
                @csrf
                @method('PUT')
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label">Kode</label>
                            <input type="text" class="form-control @error('kode') is-invalid @enderror" name="kode"
                                value="{{ old('kode', $supplier->kode) }}" />
                            <span class="form-text text-muted">Kode akan otomatis diawali dengan 'SUP'</span>
                            @error('kode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Nama</label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama"
                                value="{{ old('nama', $supplier->nama) }}" required />
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Telepon</label>
                            <input type="text" class="form-control @error('telepone') is-invalid @enderror"
                                name="telepone" value="{{ old('telepone', $supplier->telepone) }}" required />
                            @error('telepone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Kota</label>
                            <input type="text" class="form-control @error('kota') is-invalid @enderror" name="kota"
                                value="{{ old('kota', $supplier->kota) }}" required />
                            @error('kota')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Lead Time (hari)</label>
                            <input type="number" class="form-control @error('lead_time') is-invalid @enderror"
                                name="lead_time" value="{{ old('lead_time', $supplier->lead_time) }}" required
                                min="0" />
                            @error('lead_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $supplier->status) == '1' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="0" {{ old('status', $supplier->status) == '0' ? 'selected' : '' }}>Non
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
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" required>{{ old('alamat', $supplier->alamat) }}</textarea>
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
            // Format nomor telepon
            $('input[name="telepone"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value.length > 0) {
                    if (value.substring(0, 1) !== '0') {
                        value = '0' + value;
                    }
                }
                $(this).val(value);
            });

            // Format kode supplier
            $('input[name="kode"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9a-zA-Z]/g, '');
                if (!value.toUpperCase().startsWith('SUP')) {
                    value = 'SUP' + value;
                } else {
                    // Pastikan SUP dalam huruf besar
                    if (value.startsWith('sup') || value.startsWith('Sup')) {
                        value = 'SUP' + value.substring(3);
                    }
                }
                $(this).val(value);
            });

            // Client-side validation with Bootstrap styling
            $("#supplierForm").validate({
                rules: {
                    kode: {
                        required: true,
                        maxlength: 15,
                        minlength: 4
                    },
                    nama: {
                        required: true,
                        maxlength: 255,
                        minlength: 3
                    },
                    alamat: {
                        required: true,
                        minlength: 5
                    },
                    kota: {
                        required: true,
                        maxlength: 100,
                        minlength: 3
                    },
                    telepone: {
                        required: true,
                        maxlength: 20,
                        minlength: 10,
                        digits: true
                    },
                    lead_time: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    kode: {
                        required: "Kode supplier harus diisi",
                        maxlength: "Kode supplier maksimal 15 karakter",
                        minlength: "Kode supplier minimal 4 karakter"
                    },
                    nama: {
                        required: "Nama supplier harus diisi",
                        maxlength: "Nama supplier maksimal 255 karakter",
                        minlength: "Nama supplier minimal 3 karakter"
                    },
                    alamat: {
                        required: "Alamat harus diisi",
                        minlength: "Alamat minimal 5 karakter"
                    },
                    kota: {
                        required: "Kota harus diisi",
                        maxlength: "Kota maksimal 100 karakter",
                        minlength: "Kota minimal 3 karakter"
                    },
                    telepone: {
                        required: "Nomor telepon harus diisi",
                        maxlength: "Nomor telepon maksimal 20 karakter",
                        minlength: "Nomor telepon minimal 10 karakter",
                        digits: "Nomor telepon hanya boleh berisi angka"
                    },
                    lead_time: {
                        required: "Lead time harus diisi",
                        number: "Lead time harus berupa angka",
                        min: "Lead time minimal 0"
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
