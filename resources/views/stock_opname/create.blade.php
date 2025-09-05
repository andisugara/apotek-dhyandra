@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Tambah Stock Opname</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Membuat data stock opname baru</span>
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('stock_opname.index') }}" class="btn btn-secondary">
                    <span class="svg-icon svg-icon-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none">
                            <path d="M9.60001 11H21C21.6 11 22 11.4 22 12C22 12.6 21.6 13 21 13H9.60001V11Z"
                                fill="currentColor" />
                            <path opacity="0.3" d="M9.6 20V4L2.3 11.3C1.9 11.7 1.9 12.3 2.3 12.7L9.6 20Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                    Kembali
                </a>
            </div>
        </div>
        <div class="card-body pt-0">
            <form action="{{ route('stock_opname.store') }}" method="POST">
                @csrf
                <div class="row mb-6">
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="required form-label">Kode Stock Opname</label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                placeholder="Kode Stock Opname" value="{{ $kode }}" readonly />
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-5">
                            <label class="required form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', date('Y-m-d')) }}" />
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row mb-6">
                    <div class="col-md-12">
                        <div class="mb-5">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3"
                                placeholder="Keterangan stock opname">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="separator separator-dashed my-5"></div>
                <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-light me-3">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Form validation logic if needed
        });
    </script>
@endsection
