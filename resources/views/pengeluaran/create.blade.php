@extends('layout.app')
@section('title', 'Tambah Pengeluaran')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tambah Pengeluaran Baru</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('pengeluaran.store') }}" method="POST" id="formPengeluaran">
                @csrf

                <div class="row mb-6">
                    <label for="nama" class="col-lg-2 col-form-label required fw-semibold fs-6">Nama Pengeluaran</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="nama" id="nama"
                            class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}"
                            placeholder="Masukkan nama pengeluaran" required />
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="akun_id" class="col-lg-2 col-form-label required fw-semibold fs-6">Akun</label>
                    <div class="col-lg-8 fv-row">
                        <select name="akun_id" id="akun_id" class="form-select @error('akun_id') is-invalid @enderror"
                            required>
                            <option value="">Pilih Akun</option>
                            @foreach ($akuns as $akun)
                                <option value="{{ $akun->id }}" {{ old('akun_id') == $akun->id ? 'selected' : '' }}>
                                    {{ $akun->kode }} - {{ $akun->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('akun_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="tanggal" class="col-lg-2 col-form-label required fw-semibold fs-6">Tanggal</label>
                    <div class="col-lg-8 fv-row">
                        <input type="date" name="tanggal" id="tanggal"
                            class="form-control @error('tanggal') is-invalid @enderror"
                            value="{{ old('tanggal', date('Y-m-d')) }}" required />
                        @error('tanggal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-6">
                    <label for="jumlah" class="col-lg-2 col-form-label required fw-semibold fs-6">Jumlah (Rp)</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="jumlah" id="jumlah"
                            class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}"
                            placeholder="Masukkan jumlah pengeluaran" required />
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mt-8">
                    <div class="col-lg-10 d-flex justify-content-end">
                        <a href="{{ route('pengeluaran.index') }}" class="btn btn-light me-3">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
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
            $("#formPengeluaran").validate({
                rules: {
                    nama: {
                        required: true,
                        maxlength: 255
                    },
                    tanggal: {
                        required: true,
                        date: true
                    },
                    jumlah: {
                        required: true
                    }
                },
                messages: {
                    nama: {
                        required: "Nama pengeluaran harus diisi",
                        maxlength: "Nama pengeluaran maksimal 255 karakter"
                    },
                    tanggal: {
                        required: "Tanggal harus diisi",
                        date: "Format tanggal tidak valid"
                    },
                    jumlah: {
                        required: "Jumlah harus diisi"
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
                },
                submitHandler: function(form) {
                    // Remove formatting from jumlah before submitting
                    var jumlah = $('#jumlah').val();
                    $('#jumlah').val(jumlah.replace(/\./g, ''));

                    form.submit();
                }
            });

            // Format currency input
            $('#jumlah').on('input', function() {
                // Remove all characters except numbers
                var value = $(this).val().replace(/[^0-9]/g, '');

                // Format with thousand separator
                if (value) {
                    value = parseInt(value).toLocaleString('id-ID');
                }

                $(this).val(value);

                // Revalidate the field
                $("#formPengeluaran").validate().element("#jumlah");
            });
        });
    </script>
@endpush
