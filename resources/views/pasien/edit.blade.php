@extends('layout.app')
@section('title', 'Edit Pasien')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Pasien</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('pasien.update', $pasien->id) }}" method="POST" id="formEditPasien">
                @csrf
                @method('PUT')

                <div class="row mb-6">
                    <label for="code" class="col-lg-2 col-form-label required fw-semibold fs-6">Kode Pasien</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="code" id="code"
                            class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code', $pasien->code) }}" required />
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="nama" class="col-lg-2 col-form-label required fw-semibold fs-6">Nama Pasien</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="nama" id="nama"
                            class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('nama', $pasien->nama) }}" required />
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-2 col-form-label required fw-semibold fs-6">Jenis Kelamin</label>
                    <div class="col-lg-8 fv-row">
                        <div class="d-flex align-items-center mt-3">
                            <label class="form-check form-check-inline form-check-solid me-5">
                                <input type="radio" name="jenis_kelamin" class="form-check-input" value="Laki-laki"
                                    {{ old('jenis_kelamin', $pasien->jenis_kelamin) == 'Laki-laki' ? 'checked' : '' }}
                                    required />
                                <span class="fw-semibold ps-2 fs-6">Laki-laki</span>
                            </label>
                            <label class="form-check form-check-inline form-check-solid">
                                <input type="radio" name="jenis_kelamin" class="form-check-input" value="Perempuan"
                                    {{ old('jenis_kelamin', $pasien->jenis_kelamin) == 'Perempuan' ? 'checked' : '' }}
                                    required />
                                <span class="fw-semibold ps-2 fs-6">Perempuan</span>
                            </label>
                        </div>
                        @error('jenis_kelamin')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="tanggal_lahir" class="col-lg-2 col-form-label required fw-semibold fs-6">Tanggal
                        Lahir</label>
                    <div class="col-lg-8 fv-row">
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                            class="form-control @error('tanggal_lahir') is-invalid @enderror"
                            value="{{ old('tanggal_lahir', $pasien->tanggal_lahir->format('Y-m-d')) }}" required />
                        @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="alamat" class="col-lg-2 col-form-label required fw-semibold fs-6">Alamat</label>
                    <div class="col-lg-8 fv-row">
                        <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"
                            required>{{ old('alamat', $pasien->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-2 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8 fv-row">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $pasien->is_active ? 'checked' : '' }} />
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-8">
                    <div class="col-lg-10 d-flex justify-content-end">
                        <a href="{{ route('pasien.index') }}" class="btn btn-light me-3">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Client-side validation
            $("#formEditPasien").validate({
                rules: {
                    code: {
                        required: true,
                        maxlength: 20
                    },
                    nama: {
                        required: true,
                        maxlength: 100
                    },
                    jenis_kelamin: {
                        required: true
                    },
                    tanggal_lahir: {
                        required: true,
                        date: true
                    },
                    alamat: {
                        required: true
                    }
                },
                messages: {
                    code: {
                        required: "Kode pasien harus diisi",
                        maxlength: "Kode maksimal 20 karakter"
                    },
                    nama: {
                        required: "Nama pasien harus diisi",
                        maxlength: "Nama maksimal 100 karakter"
                    },
                    jenis_kelamin: {
                        required: "Jenis kelamin harus dipilih"
                    },
                    tanggal_lahir: {
                        required: "Tanggal lahir harus diisi",
                        date: "Format tanggal lahir tidak valid"
                    },
                    alamat: {
                        required: "Alamat harus diisi"
                    }
                },
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    if (element.prop("type") === "radio") {
                        error.insertAfter(element.parent().parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                }
            });
        });
    </script>
@endpush
