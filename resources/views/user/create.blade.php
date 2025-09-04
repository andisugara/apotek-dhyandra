@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah User</h3>
            <div class="card-toolbar">
                <a href="{{ route('user.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('user.store') }}" method="POST" id="userForm">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name') }}" required />
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" required />
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label">Telepon</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                                value="{{ old('phone') }}" />
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Role</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Pilih Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required />
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">Status</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="is_active" {{ old('is_active', '1') == '1' ? 'checked' : '' }} />
                                <label class="form-check-label" for="is_active">
                                    Aktif
                                </label>
                            </div>
                            @error('is_active')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
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
            // Format nomor telepon
            $('input[name="phone"]').on('input', function() {
                let value = $(this).val().replace(/[^0-9]/g, '');
                if (value.length > 0) {
                    if (value.substring(0, 1) !== '0') {
                        value = '0' + value;
                    }
                }
                $(this).val(value);
            });

            // Handling is_active checkbox
            $('#userForm').on('submit', function() {
                if (!$('#is_active').is(':checked')) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'is_active',
                        value: '0'
                    }).appendTo('#userForm');
                }
            });

            // Client-side validation
            $("#userForm").validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 255
                    },
                    phone: {
                        digits: true,
                        maxlength: 20
                    },
                    password: {
                        required: true,
                        minlength: 8
                    },
                    password_confirmation: {
                        required: true,
                        equalTo: "input[name='password']"
                    },
                    role_id: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Nama harus diisi",
                        minlength: "Nama minimal 3 karakter",
                        maxlength: "Nama maksimal 255 karakter"
                    },
                    email: {
                        required: "Email harus diisi",
                        email: "Format email tidak valid",
                        maxlength: "Email maksimal 255 karakter"
                    },
                    phone: {
                        digits: "Telepon hanya boleh berisi angka",
                        maxlength: "Telepon maksimal 20 karakter"
                    },
                    password: {
                        required: "Password harus diisi",
                        minlength: "Password minimal 8 karakter"
                    },
                    password_confirmation: {
                        required: "Konfirmasi password harus diisi",
                        equalTo: "Konfirmasi password harus sama dengan password"
                    },
                    role_id: {
                        required: "Role harus dipilih"
                    }
                },
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");

                    if (element.parent(".input-group").length) {
                        error.insertAfter(element.parent());
                    } else if (element.hasClass("form-select")) {
                        error.insertAfter(element.next("span"));
                    } else if (element.hasClass("form-check-input")) {
                        error.insertAfter(element.closest(".form-check"));
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
