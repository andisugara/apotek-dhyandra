@extends('layout.app')
@section('title', 'Detail Golongan Obat')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Golongan Obat</h3>
        </div>
        <div class="card-body">
            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Nama</label>
                <div class="col-lg-8 fs-6">{{ $golongan_obat->nama }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Keterangan</label>
                <div class="col-lg-8 fs-6">{{ $golongan_obat->keterangan ?: '-' }}</div>
            </div>

            <div class="row mb-6">
                <label class="col-lg-2 fw-semibold fs-6">Status</label>
                <div class="col-lg-8 fs-6">
                    @if ($golongan_obat->is_active)
                        <span class="badge badge-light-success">Aktif</span>
                    @else
                        <span class="badge badge-light-danger">Non-Aktif</span>
                    @endif
                </div>
            </div>

            <div class="row mt-8">
                <div class="col-lg-10 d-flex justify-content-end">
                    <a href="{{ route('golongan_obat.index') }}" class="btn btn-light me-3">Kembali</a>
                    <a href="{{ route('golongan_obat.edit', $golongan_obat->id) }}" class="btn btn-primary">Edit</a>
                </div>
            </div>
        </div>
    </div>
@endsection
