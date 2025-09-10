@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Detail Pembelian</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('pembelian.index') }}" class="btn btn-sm btn-light-primary me-2">
                    <i class="ki-duotone ki-arrow-left fs-3"></i>Kembali
                </a>
                <a href="{{ route('pembelian.edit', $pembelian->id) }}" class="btn btn-sm btn-primary me-2">
                    <i class="ki-duotone ki-pencil fs-3"></i>Edit
                </a>
                <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="{{ $pembelian->id }}">
                    <i class="ki-duotone ki-trash fs-3"></i>Hapus
                </button>
            </div>
        </div>
        <div class="card-body py-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Data Pembelian -->
            <div class="row mb-6">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25 fw-bolder text-muted">No Faktur</th>
                            <td>: {{ $pembelian->no_faktur }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 fw-bolder text-muted">No PO</th>
                            <td>: {{ $pembelian->no_po ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 fw-bolder text-muted">Tanggal</th>
                            <td>: {{ $pembelian->tanggal_faktur->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="w-25 fw-bolder text-muted">Jenis Pembayaran</th>
                            <td>:
                                @if ($pembelian->jenis == 'TUNAI')
                                    <span class="badge badge-light-success">TUNAI</span>
                                @elseif ($pembelian->jenis == 'HUTANG')
                                    <span class="badge badge-light-warning">HUTANG</span>
                                @else
                                    <span class="badge badge-light-info">KONSINYASI</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th class="w-25 fw-bolder text-muted">Supplier</th>
                            <td>: {{ $pembelian->supplier->nama }}</td>
                        </tr>
                        @if ($pembelian->jenis == 'TUNAI')
                            <tr>
                                <th class="w-25 fw-bolder text-muted">Akun Kas</th>
                                <td>:
                                    {{ $pembelian->akunKas ? $pembelian->akunKas->kode . ' - ' . $pembelian->akunKas->nama : '-' }}
                                </td>
                            </tr>
                        @elseif ($pembelian->jenis == 'HUTANG')
                            <tr>
                                <th class="w-25 fw-bolder text-muted">Jatuh Tempo</th>
                                <td>:
                                    {{ $pembelian->tanggal_jatuh_tempo ? $pembelian->tanggal_jatuh_tempo->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-25 fw-bolder text-muted">Status Jatuh Tempo</th>
                                <td>:
                                    @php
                                        $status = $pembelian->status_jatuh_tempo;
                                        $class = '';
                                        switch ($status) {
                                            case 'TERLAMBAT':
                                                $class = 'badge-light-danger';
                                                break;
                                            case 'JATUH TEMPO HARI INI':
                                                $class = 'badge-light-warning';
                                                break;
                                            case 'MENDEKATI JATUH TEMPO':
                                                $class = 'badge-light-info';
                                                break;
                                            default:
                                                $class = 'badge-light-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $class }}">{{ $status }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="w-25 fw-bolder text-muted">Status Pembayaran</th>
                                <td>: {!! $pembelian->status_pembayaran_formatted !!}</td>
                            </tr>
                        @endif
                        <tr>
                            <th class="w-25 fw-bolder text-muted">Petugas</th>
                            <td>: {{ $pembelian->user->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="separator separator-dashed my-5"></div>

            <!-- Detail Pembelian -->
            <h4 class="mb-5">Detail Item</h4>

            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-100px">Obat</th>
                            <th class="min-w-100px">Satuan</th>
                            <th class="min-w-50px text-end">Jumlah</th>
                            <th class="min-w-100px text-end">Harga Beli</th>
                            <th class="min-w-100px text-end">Subtotal</th>
                            <th class="min-w-50px text-end">Diskon %</th>
                            <th class="min-w-100px text-end">Diskon Rp</th>
                            <th class="min-w-100px text-end">HPP</th>
                            <th class="min-w-50px text-end">Margin %</th>
                            <th class="min-w-100px text-end">Harga Jual</th>
                            <th class="min-w-80px">No Batch</th>
                            <th class="min-w-80px">Expired</th>
                            <th class="min-w-100px text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pembelian->details as $detail)
                            <tr>
                                <td>{{ $detail->obat->nama_obat }}</td>
                                <td>{{ $detail->satuan->nama }}</td>
                                <td class="text-end">{{ $detail->jumlah }}</td>
                                <td class="text-end">Rp {{ $detail->formatted_harga_beli }}</td>
                                <td class="text-end">Rp {{ $detail->formatted_subtotal }}</td>
                                <td class="text-end">{{ $detail->diskon_persen }}%</td>
                                <td class="text-end">Rp {{ $detail->formatted_diskon_nominal }}</td>
                                <td class="text-end">Rp {{ $detail->formatted_hpp_per_unit }}</td>
                                <td class="text-end">{{ $detail->margin_jual_persen }}%</td>
                                <td class="text-end">Rp {{ $detail->formatted_harga_jual_per_unit }}</td>
                                <td>{{ $detail->no_batch }}</td>
                                <td>{{ $detail->tanggal_expired->format('d/m/Y') }}</td>
                                <td class="text-end">Rp {{ $detail->formatted_total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="12" class="text-end">Subtotal</td>
                            <td class="text-end">Rp {{ $pembelian->formatted_subtotal }}</td>
                        </tr>
                        <tr>
                            <td colspan="12" class="text-end">Diskon Total</td>
                            <td class="text-end">Rp {{ $pembelian->formatted_diskon_total }}</td>
                        </tr>
                        <tr>
                            <td colspan="12" class="text-end">PPN</td>
                            <td class="text-end">Rp {{ $pembelian->formatted_ppn_total }}</td>
                        </tr>
                        <tr class="fw-bolder fs-6">
                            <td colspan="12" class="text-end">Grand Total</td>
                            <td class="text-end">Rp {{ $pembelian->formatted_grand_total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($pembelian->jenis == 'HUTANG' && $pembelian->status_pembayaran != 'LUNAS')
                <div class="separator separator-dashed my-5"></div>

                <h4 class="mb-5">Update Status Pembayaran</h4>

                <div class="card card-bordered">
                    <div class="card-body">
                        <form action="{{ route('pembelian.update-status', $pembelian->id) }}" method="POST">
                            @csrf
                            <div class="row mb-6">
                                <div class="col-lg-6">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Status Pembayaran</label>
                                        <select name="status_pembayaran" class="form-select">
                                            <option value="BELUM"
                                                {{ $pembelian->status_pembayaran == 'BELUM' ? 'selected' : '' }}>BELUM
                                                LUNAS</option>
                                            {{-- <option value="SEBAGIAN"
                                                {{ $pembelian->status_pembayaran == 'SEBAGIAN' ? 'selected' : '' }}>DIBAYAR
                                                SEBAGIAN</option> --}}
                                            <option value="LUNAS"
                                                {{ $pembelian->status_pembayaran == 'LUNAS' ? 'selected' : '' }}>LUNAS
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-5">
                                        <label class="form-label fw-bold">Akun Kas</label>
                                        <select name="akun_kas_id" class="form-select">
                                            <option value="">-- Pilih Akun Kas --</option>
                                            @foreach (App\Models\Akun::where('status', '1')->orderBy('nama')->get() as $akun)
                                                <option value="{{ $akun->id }}">{{ $akun->kode }} -
                                                    {{ $akun->nama }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-muted">Diperlukan untuk status DIBAYAR SEBAGIAN atau
                                            LUNAS.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if ($pembelian->transaksiAkun->count() > 0)
                <div class="separator separator-dashed my-5"></div>

                <h4 class="mb-5">Transaksi Akuntansi</h4>

                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="min-w-150px">Kode Referensi</th>
                                <th class="min-w-150px">Tanggal</th>
                                <th class="min-w-150px">Akun</th>
                                <th class="min-w-150px">Deskripsi</th>
                                <th class="min-w-150px text-end">Debit</th>
                                <th class="min-w-150px text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pembelian->transaksiAkun as $transaksi)
                                <tr>
                                    <td>{{ $transaksi->kode_referensi }}</td>
                                    <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $transaksi->akun->kode }} - {{ $transaksi->akun->nama }}</td>
                                    <td>{{ $transaksi->deskripsi }}</td>
                                    <td class="text-end">Rp {{ $transaksi->formatted_debit }}</td>
                                    <td class="text-end">Rp {{ $transaksi->formatted_kredit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin menghapus data pembelian ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" action="{{ route('pembelian.destroy', $pembelian->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Delete confirmation
            $(document).on('click', '.btn-delete', function() {
                $('#deleteModal').modal('show');
            });

            // Handle payment status change
            $('select[name="status_pembayaran"]').change(function() {
                const status = $(this).val();
                if (status === 'BELUM') {
                    $('select[name="akun_kas_id"]').prop('required', false);
                } else {
                    $('select[name="akun_kas_id"]').prop('required', true);
                }
            });

            // Initialize form validation
            $('form[action*="update-status"]').submit(function(e) {
                const status = $('select[name="status_pembayaran"]').val();
                const akunKasId = $('select[name="akun_kas_id"]').val();

                if ((status === 'SEBAGIAN' || status === 'LUNAS') && !akunKasId) {
                    e.preventDefault();
                    alert('Pilih Akun Kas untuk status pembayaran ' + status);
                    return false;
                }

                return true;
            });

            // Initial check
            $('select[name="status_pembayaran"]').trigger('change');
        });
    </script>
@endpush
