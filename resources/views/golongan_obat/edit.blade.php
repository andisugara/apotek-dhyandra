@extends('layout.app')
@section('title', 'Edit Golongan Obat')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Golongan Obat</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('golongan_obat.update', $golongan_obat->id) }}" method="POST" id="formEditGolonganObat">
                @csrf
                @method('PUT')

                <div class="row mb-6">
                    <label for="nama" class="col-lg-2 col-form-label required fw-semibold fs-6">Nama</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="nama" id="nama"
                            class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('nama', $golongan_obat->nama) }}" required />
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="keterangan" class="col-lg-2 col-form-label fw-semibold fs-6">Keterangan</label>
                    <div class="col-lg-8 fv-row">
                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                            rows="3">{{ old('keterangan', $golongan_obat->keterangan) }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-2 col-form-label fw-semibold fs-6">Status</label>
                    <div class="col-lg-8 fv-row">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $golongan_obat->is_active ? 'checked' : '' }} />
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-8">
                    <div class="col-lg-10 d-flex justify-content-end">
                        <a href="{{ route('golongan_obat.index') }}" class="btn btn-light me-3">Batal</a>
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
            $("#formEditGolonganObat").validate({
                rules: {
                    nama: {
                        required: true,
                        maxlength: 100
                    }
                },
                messages: {
                    nama: {
                        required: "Nama golongan obat harus diisi",
                        maxlength: "Nama maksimal 100 karakter"
                    }
                },
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    error.insertAfter(element);
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
