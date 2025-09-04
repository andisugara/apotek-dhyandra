@extends('layout.app')
@section('title', 'Detail Pasien')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Pasien</h3>
        </div>
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Kode Pasien</label>
                <div class="col-lg-8 fs-6">{{ $pasien->code }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Nama Pasien</label>
                <div class="col-lg-8 fs-6">{{ $pasien->nama }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Jenis Kelamin</label>
                <div class="col-lg-8 fs-6">{{ $pasien->jenis_kelamin }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Tanggal Lahir</label>
                <div class="col-lg-8 fs-6">{{ $pasien->tanggal_lahir->format('d-m-Y') }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Alamat</label>
                <div class="col-lg-8 fs-6">{{ $pasien->alamat }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Status</label>
                <div class="col-lg-8 fs-6">
                    @if ($pasien->is_active)
                        <span class="badge badge-light-success">Aktif</span>
                    @else
                        <span class="badge badge-light-danger">Non-Aktif</span>
                    @endif
                </div>
            </div>

            <div class="row mt-8">
                <div class="col-lg-10 d-flex justify-content-end">
                    <a href="{{ route('pasien.index') }}" class="btn btn-light me-3">Kembali</a>
                    <a href="{{ route('pasien.edit', $pasien->id) }}" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
@endsection
