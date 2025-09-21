@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Import Data Obat</h3>
            <div class="card-toolbar">
                {{-- <a href="{{ route('obat.template') }}" class="btn btn-info mt-3">
                    <i class="ki-duotone ki-file-down fs-2"></i>Download Template
                </a> --}}
                <a href="{{ route('obat.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-8">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-dark">Petunjuk Import</h4>
                            <p>Untuk mengimport data obat, silahkan ikuti langkah-langkah berikut:</p>
                            <ol>
                                <li>Download Excel dari aplikasi sebelumnya</b>.</li>
                                <li>Upload file Excel yang sudah diisi dengan menekan tombol <b>Browse</b>.</li>
                                <li>Tekan tombol <b>Import</b> untuk memulai proses import.</li>
                            </ol>
                            <p>Catatan: Untuk setiap satuan (1-4), hanya <b>Harga 1</b> yang akan diambil sebagai harga
                                jual. <b>Harga 2</b> dan <b>Harga 3</b> diabaikan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('obat.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="form-label required">File Excel</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" name="file"
                                required />
                            @error('file')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <span class="form-text text-muted">Format file: .xlsx, .xls (maks. 10MB)</span>
                        </div>
                    </div>
                </div>

                @if (session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning">
                        <h4>Beberapa data gagal diimport:</h4>
                        <ul>
                            @foreach (session('import_errors') as $error)
                                <li>Baris {{ $error['row'] }}: {{ $error['message'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-light me-3" onclick="window.history.back();">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Import</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Client-side validation with Bootstrap styling
            $("#importForm").validate({
                rules: {
                    file: {
                        required: true,
                        extension: "xlsx|xls"
                    }
                },
                messages: {
                    file: {
                        required: "File Excel harus diupload",
                        extension: "Format file harus .xlsx atau .xls"
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
                },
                submitHandler: function(form) {
                    // Show loading state on button
                    var btn = $(form).find('[type="submit"]');
                    var loadingText =
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengimport...';
                    btn.html(loadingText);
                    btn.attr('disabled', true);

                    form.submit();
                }
            });

            // Add extension validation method
            $.validator.addMethod(
                "extension",
                function(value, element, param) {
                    param = typeof param === "string" ? param.replace(/,/g, "|") : "png|jpe?g|gif";
                    return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
                },
                $.validator.format("Format file tidak valid.")
            );
        });
    </script>
@endpush
