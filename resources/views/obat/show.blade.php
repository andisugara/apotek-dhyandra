@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Obat</h3>
            <div class="card-toolbar">
                <a href="{{ route('obat.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2"></i>Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab_obat">Data Obat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_satuan">Satuan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab_stok">Stok</a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab Obat -->
                <div class="tab-pane fade show active" id="tab_obat" role="tabpanel">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Kode Obat:</label>
                                <span class="fs-6">{{ $obat->kode_obat }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Nama Obat:</label>
                                <span class="fs-6">{{ $obat->nama_obat }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Pabrik:</label>
                                <span class="fs-6">{{ $obat->pabrik->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Golongan:</label>
                                <span class="fs-6">{{ $obat->golongan->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Kategori:</label>
                                <span class="fs-6">{{ $obat->kategori->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Jenis Obat:</label>
                                <span class="fs-6">{{ $obat->jenis_obat }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Minimal Stok:</label>
                                <span class="fs-6">{{ $obat->minimal_stok }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Status:</label>
                                <span
                                    class="badge {{ $obat->is_active == '1' ? 'badge-light-success' : 'badge-light-danger' }}">
                                    {{ $obat->status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Kemasan:</label>
                                <span class="fs-6">{{ $obat->kemasan ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="separator my-7"></div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-7">
                                <label class="fw-bold">Deskripsi:</label>
                                <div class="fs-6 mt-2">{{ $obat->deskripsi ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Indikasi:</label>
                                <div class="fs-6 mt-2">{{ $obat->indikasi ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Kandungan:</label>
                                <div class="fs-6 mt-2">{{ $obat->kandungan ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Dosis:</label>
                                <div class="fs-6 mt-2">{{ $obat->dosis ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Efek Samping:</label>
                                <div class="fs-6 mt-2">{{ $obat->efek_samping ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Zat Aktif/Prekursor:</label>
                                <div class="fs-6 mt-2">{{ $obat->zat_aktif_prekursor ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-7">
                                <label class="fw-bold">Aturan Pakai:</label>
                                <div class="fs-6 mt-2">{{ $obat->aturan_pakai ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Satuan -->
                <div class="tab-pane fade" id="tab_satuan" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>No</th>
                                    <th>Satuan</th>
                                    <th>Harga Beli</th>
                                    <th>Diskon (%)</th>
                                    <th>Profit (%)</th>
                                    <th>Harga Jual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($obat->satuans as $index => $satuanObat)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $satuanObat->satuan->nama }}</td>
                                        <td>Rp{{ number_format($satuanObat->harga_beli, 0, ',', '.') }}</td>
                                        <td>{{ $satuanObat->diskon_persen }}%</td>
                                        <td>{{ $satuanObat->profit_persen ?? 10 }}%</td>
                                        <td>Rp{{ number_format($satuanObat->harga_jual, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data satuan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Stok -->
                <div class="tab-pane fade" id="tab_stok" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>No</th>
                                    <th>Satuan</th>
                                    <th>Lokasi</th>
                                    <th>No. Batch</th>
                                    <th>Tanggal Expired</th>
                                    <th>Qty</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($obat->stok as $index => $stokItem)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $stokItem->satuan->nama }}</td>
                                        <td>{{ $stokItem->lokasi->nama }}</td>
                                        <td>{{ $stokItem->no_batch }}</td>
                                        <td>{{ $stokItem->tanggal_expired->format('d/m/Y') }}</td>
                                        <td>{{ $stokItem->qty }}</td>
                                        <td>Rp{{ number_format($stokItem->harga_beli, 0, ',', '.') }}</td>
                                        <td>Rp{{ number_format($stokItem->harga_jual, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($stokItem->is_expired)
                                                <span class="badge badge-light-danger">Expired</span>
                                            @elseif($stokItem->days_until_expired < 30)
                                                <span class="badge badge-light-warning">Mendekati Expired
                                                    ({{ $stokItem->days_until_expired }} hari)
                                                </span>
                                            @else
                                                <span class="badge badge-light-success">Baik</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada data stok</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-10">
                <a href="{{ route('obat.edit', $obat->id) }}" class="btn btn-primary me-3">
                    <i class="ki-duotone ki-pencil fs-2"></i>Edit
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="ki-duotone ki-trash fs-2"></i>Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin menghapus data obat ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('obat.destroy', $obat->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
