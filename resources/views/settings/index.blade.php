@extends('layout.app')

@section('title', 'Pengaturan Apotek')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pengaturan Apotek</h3>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm"
                class="pb-3">
                @csrf
                @method('PUT')

                <div class="row mb-6">
                    <label for="nama_apotek" class="col-lg-2 col-form-label required fw-semibold fs-6">Nama Apotek</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" class="form-control @error('nama_apotek') is-invalid @enderror"
                            id="nama_apotek" name="nama_apotek"
                            value="{{ old('nama_apotek', $setting->nama_apotek ?? '') }}" placeholder="Masukkan nama apotek"
                            {{ Auth::user()->hasRole('Superadmin') ? '' : 'disabled' }}>
                        @error('nama_apotek')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="alamat" class="col-lg-2 col-form-label required fw-semibold fs-6">Alamat</label>
                    <div class="col-lg-8 fv-row">
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3"
                            placeholder="Masukkan alamat lengkap apotek" {{ Auth::user()->hasRole('Superadmin') ? '' : 'disabled' }}>{{ old('alamat', $setting->alamat ?? '') }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="telepon" class="col-lg-2 col-form-label required fw-semibold fs-6">Telepon</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon"
                            name="telepon" value="{{ old('telepon', $setting->telepon ?? '') }}"
                            placeholder="Masukkan nomor telepon"
                            {{ Auth::user()->hasRole('Superadmin') ? '' : 'disabled' }}>
                        @error('telepon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="email" class="col-lg-2 col-form-label required fw-semibold fs-6">Email</label>
                    <div class="col-lg-8 fv-row">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $setting->email ?? '') }}"
                            placeholder="Masukkan alamat email"
                            {{ Auth::user()->hasRole('Superadmin') ? '' : 'disabled' }}>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="logo" class="col-lg-2 col-form-label fw-semibold fs-6">Logo</label>
                    <div class="col-lg-8 fv-row">
                        <div id="logoPreviewContainer"
                            class="mb-4 {{ isset($setting->logo) && $setting->logo ? '' : 'd-none' }}">
                            <img id="logoPreview"
                                src="{{ isset($setting->logo) && $setting->logo ? asset('storage/' . $setting->logo) : '' }}"
                                alt="Logo Apotek" class="img-thumbnail" style="max-height: 100px;">
                        </div>

                        @if (Auth::user()->hasRole('Superadmin'))
                            <div class="input-group">
                                <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                    id="logo" name="logo">
                            </div>
                            <small class="text-muted d-block mt-2">Format yang diizinkan: JPG, JPEG, PNG. Ukuran maksimal:
                                2MB</small>
                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>

                @if (Auth::user()->hasRole('Superadmin'))
                    <div class="row mt-8">
                        <div class="col-lg-10 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @if (Auth::user()->hasRole('Superadmin'))
        <script>
            $(document).ready(function() {
                // Preview logo when file is selected
                $("#logo").on("change", function(event) {
                    var file = event.target.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $("#logoPreview").attr("src", e.target.result);
                            $("#logoPreviewContainer").removeClass("d-none");
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Client-side validation with jQuery Validate
                $("#settingsForm").validate({
                    rules: {
                        nama_apotek: {
                            required: true,
                            maxlength: 255
                        },
                        alamat: {
                            required: true,
                            maxlength: 500
                        },
                        telepon: {
                            required: true,
                            maxlength: 20
                        },
                        email: {
                            required: true,
                            email: true,
                            maxlength: 255
                        },
                        logo: {
                            extension: "jpg|jpeg|png",
                            filesize: 2097152 // 2MB
                        }
                    },
                    messages: {
                        nama_apotek: {
                            required: "Nama apotek harus diisi",
                            maxlength: "Nama apotek maksimal 255 karakter"
                        },
                        alamat: {
                            required: "Alamat harus diisi",
                            maxlength: "Alamat maksimal 500 karakter"
                        },
                        telepon: {
                            required: "Nomor telepon harus diisi",
                            maxlength: "Nomor telepon maksimal 20 karakter"
                        },
                        email: {
                            required: "Email harus diisi",
                            email: "Format email tidak valid",
                            maxlength: "Email maksimal 255 karakter"
                        },
                        logo: {
                            extension: "Format file harus JPG, JPEG, atau PNG",
                            filesize: "Ukuran file maksimal 2MB"
                        }
                    },
                    errorElement: "div",
                    errorPlacement: function(error, element) {
                        error.addClass("invalid-feedback");
                        error.insertAfter(element);
                    },
                    highlight: function(element, errorClass, validClass) {
                        $(element).addClass("is-invalid").removeClass("is-valid");

                        // Add animation to the form group
                        $(element).closest('.fv-row').addClass('animate__animated animate__headShake');
                        setTimeout(function() {
                            $('.animate__animated').removeClass(
                                'animate__animated animate__headShake');
                        }, 1000);
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        $(element).removeClass("is-invalid").addClass("is-valid");
                    }
                });

                // Add custom validation method for file size
                $.validator.addMethod("filesize", function(value, element, param) {
                    if (element.files.length > 0) {
                        return element.files[0].size <= param;
                    }
                    return true; // Skip validation if no file
                }, "File size must be less than {0} bytes");

                // Format nomor telepon
                $('#telepon').on('input', function() {
                    let value = $(this).val().replace(/[^0-9]/g, '');
                    if (value.length > 0) {
                        if (value.substring(0, 1) !== '0') {
                            value = '0' + value;
                        }
                    }
                    $(this).val(value);
                });
            });
        </script>
    @endif
@endpush
