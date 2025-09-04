@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Akun</h3>
            <div class="card-toolbar">
                <a href="{{ route('akun.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-7">
                <label class="col-lg-4 fw-bold text-muted">Kode</label>
                <div class="col-lg-8">
                    <span class="fw-bolder fs-6 text-dark">{{ $akun->kode }}</span>
                </div>
            </div>

            <div class="row mb-7">
                <label class="col-lg-4 fw-bold text-muted">Nama</label>
                <div class="col-lg-8">
                    <span class="fw-bolder fs-6 text-dark">{{ $akun->nama }}</span>
                </div>
            </div>

            <div class="row mb-7">
                <label class="col-lg-4 fw-bold text-muted">Status</label>
                <div class="col-lg-8">
                    <span class="badge badge-{{ $akun->status == '1' ? 'success' : 'danger' }}">
                        {{ $akun->status_label }}
                    </span>
                </div>
            </div>

            <div class="row mb-7">
                <label class="col-lg-4 fw-bold text-muted">Dibuat Pada</label>
                <div class="col-lg-8">
                    <span class="fw-bolder fs-6 text-dark">{{ $akun->created_at->format('d M Y H:i') }}</span>
                </div>
            </div>

            <div class="row mb-7">
                <label class="col-lg-4 fw-bold text-muted">Terakhir Diupdate</label>
                <div class="col-lg-8">
                    <span class="fw-bolder fs-6 text-dark">{{ $akun->updated_at->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-end">
                <a href="{{ route('akun.edit', $akun->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>
    </div>
@endsection
