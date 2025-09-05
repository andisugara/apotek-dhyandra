@extends('layout.app')

@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Stock Opname</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Daftar stock opname</span>
                </h3>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-datatable-table-toolbar="base">
                    <a href="{{ route('stock_opname.create') }}" class="btn btn-primary">
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none">
                                <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                    transform="rotate(-90 11.364 20.364)" fill="currentColor"></rect>
                                <rect x="4.36396" y="11.364" width="16" height="2" rx="1"
                                    fill="currentColor"></rect>
                            </svg>
                        </span>
                        Tambah Stock Opname
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="stock_opname_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-125px">Kode</th>
                            <th class="min-w-125px">Tanggal</th>
                            <th class="min-w-125px">Petugas</th>
                            <th class="min-w-125px">Status</th>
                            <th class="min-w-125px">Keterangan</th>
                            <th class="text-end min-w-100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach ($stockOpnames as $opname)
                            <tr>
                                <td>{{ $opname->kode }}</td>
                                <td>{{ date('d/m/Y', strtotime($opname->tanggal)) }}</td>
                                <td>{{ $opname->user->name }}</td>
                                <td>
                                    @if ($opname->status == 'draft')
                                        <span class="badge badge-light-warning">Draft</span>
                                    @else
                                        <span class="badge badge-light-success">Selesai</span>
                                    @endif
                                </td>
                                <td>{{ $opname->keterangan }}</td>
                                <td class="text-end">
                                    <a href="{{ route('stock_opname.show', $opname) }}"
                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                        title="Lihat Detail">
                                        <span class="svg-icon svg-icon-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2"
                                                    rx="1" transform="rotate(45 17.0365 15.1223)"
                                                    fill="currentColor"></rect>
                                                <path
                                                    d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                    fill="currentColor"></path>
                                            </svg>
                                        </span>
                                    </a>
                                    @if ($opname->status == 'draft')
                                        <a href="{{ route('stock_opname.edit', $opname) }}"
                                            class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1"
                                            title="Edit">
                                            <span class="svg-icon svg-icon-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path opacity="0.3"
                                                        d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z"
                                                        fill="currentColor"></path>
                                                    <path
                                                        d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z"
                                                        fill="currentColor"></path>
                                                </svg>
                                            </span>
                                        </a>
                                        <form method="POST" action="{{ route('stock_opname.destroy', $opname) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus stock opname ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm"
                                                title="Hapus">
                                                <span class="svg-icon svg-icon-3">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z"
                                                            fill="currentColor"></path>
                                                        <path opacity="0.5"
                                                            d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z"
                                                            fill="currentColor"></path>
                                                        <path opacity="0.5"
                                                            d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z"
                                                            fill="currentColor"></path>
                                                    </svg>
                                                </span>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('stock_opname.print', $opname) }}"
                                        class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="Print"
                                        target="_blank">
                                        <span class="svg-icon svg-icon-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path opacity="0.3"
                                                    d="M18.9 9.5C18.9 8.1 17.8 7 16.4 7H5.6C4.2 7 3.1 8.1 3.1 9.5C3.1 10.9 4.2 12 5.6 12H16.4C17.8 12 18.9 10.9 18.9 9.5ZM16.4 10.5C16.4 11.3 15.7 12 14.9 12C14.1 12 13.4 11.3 13.4 10.5C13.4 9.7 14.1 9 14.9 9C15.7 9 16.4 9.7 16.4 10.5Z"
                                                    fill="currentColor"></path>
                                                <path d="M7.4 21H14.6V13.9H7.4V21ZM7.4 6V1H14.6V6H7.4Z"
                                                    fill="currentColor"></path>
                                            </svg>
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $("#stock_opname_table").DataTable({
                "language": {
                    "lengthMenu": "Show _MENU_",
                },
                "dom": "<'row'" +
                    "<'col-sm-6 d-flex align-items-center justify-content-start'l>" +
                    "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
                    ">" +
                    "<'table-responsive'tr>" +
                    "<'row'" +
                    "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
                    "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
                    ">"
            });
        });
    </script>
@endsection
