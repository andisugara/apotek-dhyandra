@extends('layout.app')
@section('title', 'Dashboard')

@section('styles')
    <style>
        .apexcharts-toolbar {
            z-index: 1 !important;
            /* Fix chart toolbar overlapping issues */
        }

        .card.hoverable {
            transition: transform .2s ease-in-out;
        }

        .card.hoverable:hover {
            transform: translateY(-5px);
        }

        /* Loading state for charts */
        .opacity-50 {
            opacity: 0.5;
            transition: opacity 0.3s ease;
            position: relative;
        }

        .opacity-50::after {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-12">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body p-6">
                    <!--begin::Heading-->
                    <div class="fs-2hx fw-bold mb-5">
                        Selamat Datang, {{ auth()->user()->name }}
                    </div>
                    <!--end::Heading-->

                    @if (session('error'))
                        <div class="alert alert-danger mb-5">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="fs-4 mb-4">
                        <p>
                            Anda login sebagai
                            <span class="fw-bold text-primary">{{ auth()->user()->role->name }}</span>
                        </p>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Statistics Widget-->
            <div class="card card-xl-stretch mb-xl-8" style="background-color: #F1FAFF">
                <!--begin::Body-->
                <div class="card-body">
                    <span class="svg-icon svg-icon-primary svg-icon-3x ms-n1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3"
                                d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.3C3.50001 2.1 3 2.20001 3 3.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z"
                                fill="currentColor"></path>
                            <path
                                d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998Z"
                                fill="currentColor"></path>
                        </svg>
                    </span>

                    <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">
                        <span id="todayTransactionsCount">{{ $todayTransactionsCount }}</span>
                    </div>
                    <div class="fw-semibold text-gray-600">Transaksi Hari Ini</div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Statistics Widget-->
            <div class="card card-xl-stretch mb-xl-8" style="background-color: #E8FFF3">
                <!--begin::Body-->
                <div class="card-body">
                    <span class="svg-icon svg-icon-success svg-icon-3x ms-n1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13H22V11C22 10.4 21.6 10 21 10Z"
                                fill="currentColor"></path>
                            <path opacity="0.3"
                                d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z"
                                fill="currentColor"></path>
                            <path opacity="0.3"
                                d="M18.1 21H5.9C5.4 21 4.9 20.6 4.8 20.1L3 13H21L19.2 20.1C19.1 20.6 18.6 21 18.1 21ZM13 18V15C13 14.4 12.6 14 12 14C11.4 14 11 14.4 11 15V18C11 18.6 11.4 19 12 19C12.6 19 13 18.6 13 18Z"
                                fill="currentColor"></path>
                        </svg>
                    </span>

                    <div class="text-success fw-bold fs-2 mb-2 mt-5">
                        Rp <span id="todayTotalSales">{{ number_format($todayTotalSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="fw-semibold text-gray-600">
                        Pendapatan Hari Ini
                        @if ($salesGrowth > 0)
                            <span class="badge badge-success ms-2">+{{ number_format($salesGrowth, 1) }}%</span>
                        @elseif($salesGrowth < 0)
                            <span class="badge badge-danger ms-2">{{ number_format($salesGrowth, 1) }}%</span>
                        @endif
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Statistics Widget-->
            <div class="card card-xl-stretch mb-xl-8" style="background-color: #FFF7E7">
                <!--begin::Body-->
                <div class="card-body">
                    <span class="svg-icon svg-icon-warning svg-icon-3x ms-n1">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.3"
                                d="M3.20001 5.91897L16.9 3.01895C17.4 2.91895 18 3.219 18.1 3.819L19.2 9.01895L3.20001 5.91897Z"
                                fill="currentColor"></path>
                            <path opacity="0.3"
                                d="M13 13.9189C13 12.2189 14.3 10.9189 16 10.9189H21C21.6 10.9189 22 11.3189 22 11.9189V15.9189C22 16.5189 21.6 16.9189 21 16.9189H16C14.3 16.9189 13 15.6189 13 13.9189ZM16 12.4189C15.2 12.4189 14.5 13.1189 14.5 13.9189C14.5 14.7189 15.2 15.4189 16 15.4189C16.8 15.4189 17.5 14.7189 17.5 13.9189C17.5 13.1189 16.8 12.4189 16 12.4189Z"
                                fill="currentColor"></path>
                            <path
                                d="M13 13.9189C13 12.2189 14.3 10.9189 16 10.9189H21V7.91895C21 6.81895 20.1 5.91895 19 5.91895H3C2.4 5.91895 2 6.31895 2 6.91895V20.9189C2 21.5189 2.4 21.9189 3 21.9189H19C20.1 21.9189 21 21.0189 21 19.9189V16.9189H16C14.3 16.9189 13 15.6189 13 13.9189Z"
                                fill="currentColor"></path>
                        </svg>
                    </span>

                    <div class="text-warning fw-bold fs-2 mb-2 mt-5">
                        Rp <span id="monthlyNetProfit">{{ number_format($monthlyNetProfit, 0, ',', '.') }}</span>
                    </div>
                    <div class="fw-semibold text-gray-600">
                        Keuntungan Bulan Ini
                        @if ($monthlyProfitGrowth > 0)
                            <span class="badge badge-success ms-2">+{{ number_format($monthlyProfitGrowth, 1) }}%</span>
                        @elseif($monthlyProfitGrowth < 0)
                            <span class="badge badge-danger ms-2">{{ number_format($monthlyProfitGrowth, 1) }}%</span>
                        @endif
                        <div class="fs-7 text-muted">(Pendapatan - HPP - Pengeluaran)</div>
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-xl-8">
            <!--begin::Chart Widget-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">Grafik Penjualan</span>
                        <span class="text-muted fw-semibold fs-7">Penjualan per periode</span>
                    </h3>
                    <div class="card-toolbar">
                        <!--begin::Menu-->
                        <div class="me-2">
                            <select id="periodSelect" class="form-select form-select-sm">
                                <option value="today">Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month" selected>Bulan Ini</option>
                                <option value="year">Tahun Ini</option>
                            </select>
                        </div>
                        <!--end::Menu-->
                    </div>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Chart-->
                    <div id="salesChart" style="height: 350px"></div>
                    <!--end::Chart-->

                    <!--begin::Stats-->
                    <div class="d-flex flex-wrap pt-5">
                        <!--begin::Stat-->
                        <div class="me-8 mb-3">
                            <span class="fs-6 text-gray-500">Total Penjualan</span>
                            <span class="fs-4 fw-bold text-gray-800 d-block">Rp <span
                                    id="chartTotalSales">{{ number_format($monthlyTotalSales, 0, ',', '.') }}</span></span>
                        </div>
                        <!--end::Stat-->

                        <!--begin::Stat-->
                        <div class="me-8 mb-3">
                            <span class="fs-6 text-gray-500">Total Pengeluaran</span>
                            <span class="fs-4 fw-bold text-gray-800 d-block">Rp <span
                                    id="chartTotalExpenses">{{ number_format($monthlyTotalExpenses, 0, ',', '.') }}</span></span>
                        </div>
                        <!--end::Stat-->

                        <!--begin::Stat-->
                        <div class="mb-3">
                            <span class="fs-6 text-gray-500">Keuntungan Bersih</span>
                            <span class="fs-4 fw-bold text-success d-block">Rp <span
                                    id="chartNetProfit">{{ number_format($monthlyNetProfit, 0, ',', '.') }}</span></span>
                            <span class="fs-7 text-muted">(Setelah HPP & Pengeluaran)</span>
                        </div>
                        <!--end::Stat-->
                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Chart Widget-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Mixed Widget-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">Pengeluaran per Kategori</span>
                        <span class="text-muted fw-semibold fs-7">Bulan Ini</span>
                    </h3>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Chart-->
                    <div id="expenseChart" style="height: 250px"></div>
                    <!--end::Chart-->

                    <!--begin::Items-->
                    <div class="mt-5">
                        @forelse($expensesByCategory as $index => $expense)
                            <div class="d-flex flex-stack mb-5">
                                <!--begin::Section-->
                                <div class="d-flex align-items-center me-2">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-30px symbol-circle me-4">
                                        <span class="symbol-label"
                                            style="background-color: {{ ['#3E97FF', '#50CD89', '#F1416C', '#FFC700', '#7239EA'][$index % 5] }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <!--end::Symbol-->

                                    <!--begin::Title-->
                                    <div>
                                        <a href="#"
                                            class="fs-6 text-gray-800 text-hover-primary fw-bold">{{ $expense->nama }}</a>
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Section-->

                                <!--begin::Label-->
                                <div class="label text-end">
                                    <span class="fs-7 fw-bold text-gray-600">Rp
                                        {{ number_format($expense->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <!--end::Label-->
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <span class="fs-6 text-gray-500">Tidak ada data pengeluaran bulan ini</span>
                            </div>
                        @endforelse
                    </div>
                    <!--end::Items-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Mixed Widget-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-xl-6">
            <!--begin::List Widget-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">Produk Terlaris</span>
                        <span class="text-muted fw-semibold fs-7">Bulan Ini</span>
                    </h3>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body pt-5">
                    @forelse($topProducts as $index => $product)
                        <div class="d-flex align-items-sm-center mb-7">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-circle symbol-50px me-5">
                                <span class="symbol-label"
                                    style="background-color: {{ ['#3E97FF', '#50CD89', '#F1416C', '#FFC700', '#7239EA'][$index % 5] }}">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <!--end::Symbol-->

                            <!--begin::Section-->
                            <div class="d-flex flex-row-fluid align-items-center flex-wrap my-lg-0">
                                <!--begin::Title-->
                                <div class="flex-grow-1 me-2">
                                    <a href="{{ route('obat.show', $product->id) }}"
                                        class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $product->nama_obat }}</a>
                                    <span class="text-muted fw-semibold d-block pt-1">Terjual {{ $product->total_qty }}
                                        item</span>
                                </div>
                                <!--end::Title-->

                                <!--begin::Label-->
                                <div class="text-end py-lg-0">
                                    <span class="text-gray-800 fw-bold fs-6">Rp
                                        {{ number_format($product->total_sales, 0, ',', '.') }}</span>
                                </div>
                                <!--end::Label-->
                            </div>
                            <!--end::Section-->
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <span class="fs-6 text-gray-500">Tidak ada data penjualan produk bulan ini</span>
                        </div>
                    @endforelse
                </div>
                <!--end::Body-->
            </div>
            <!--end::List Widget-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-6">
            <!--begin::List Widget-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">Transaksi Terbaru</span>
                        <span class="text-muted fw-semibold fs-7">5 transaksi terakhir</span>
                    </h3>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body pt-5">
                    @forelse($recentTransactions as $transaction)
                        <div class="d-flex align-items-center mb-7">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-circle symbol-50px me-5">
                                <span class="symbol-label" style="background-color: #F1F1F2">
                                    <i class="ki-duotone ki-receipt text-primary fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <!--end::Avatar-->

                            <!--begin::Text-->
                            <div class="flex-grow-1">
                                <a href="{{ route('penjualan.show', $transaction->id) }}"
                                    class="text-dark fw-bold text-hover-primary fs-6">{{ $transaction->no_faktur }}</a>
                                <span
                                    class="text-muted d-block fw-bold">{{ $transaction->tanggal_penjualan->format('d/m/Y H:i') }}</span>
                            </div>
                            <!--end::Text-->

                            <!--begin::Amount-->
                            <div class="text-end">
                                <span class="text-gray-800 fw-bold fs-6">Rp
                                    {{ number_format($transaction->grand_total, 0, ',', '.') }}</span>
                                <span
                                    class="text-muted d-block fw-semibold">{{ $transaction->pasien ? $transaction->pasien->nama : 'Umum' }}</span>
                            </div>
                            <!--end::Amount-->
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <span class="fs-6 text-gray-500">Tidak ada transaksi terbaru</span>
                        </div>
                    @endforelse
                </div>
                <!--end::Body-->
            </div>
            <!--end::List Widget-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-12">
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Assets Summary</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Ringkasan nilai aset inventori</span>
                    </h3>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <div class="row g-5 g-xl-8">
                        <!-- Total Assets Card -->
                        <div class="col-xl-4">
                            <div class="card card-xl-stretch mb-xl-8 bg-light-primary">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-primary">
                                                <i class="ki-duotone ki-dollar text-inverse-primary fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold">Total Assets</div>
                                            <div class="fs-7 text-muted fw-semibold">Total nilai inventori saat ini</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column mt-7">
                                        <span class="fs-2x fw-bold text-dark me-2 lh-1 ls-n2">Rp
                                            {{ number_format($totalAssets, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assets Hutang Card -->
                        <div class="col-xl-4">
                            <div class="card card-xl-stretch mb-xl-8 bg-light-warning">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-warning">
                                                <i class="ki-duotone ki-credit-cart text-inverse-warning fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold">Assets Hutang</div>
                                            <div class="fs-7 text-muted fw-semibold">Nilai inventori dari pembelian kredit
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column mt-7">
                                        <span class="fs-2x fw-bold text-dark me-2 lh-1 ls-n2">Rp
                                            {{ number_format($totalAssetsHutang, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assets Konsinyasi Card -->
                        <div class="col-xl-4">
                            <div class="card card-xl-stretch mb-xl-8 bg-light-info">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-info">
                                                <i class="ki-duotone ki-handcart text-inverse-info fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold">Assets Konsinyasi</div>
                                            <div class="fs-7 text-muted fw-semibold">Nilai inventori konsinyasi</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column mt-7">
                                        <span class="fs-2x fw-bold text-dark me-2 lh-1 ls-n2">Rp
                                            {{ number_format($totalAssetsKonsinyasi, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assets Tunai Card -->
                        <div class="col-xl-6">
                            <div class="card card-xl-stretch mb-xl-8 bg-light-success">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-success">
                                                <i class="ki-duotone ki-wallet text-inverse-success fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold">Assets Tunai</div>
                                            <div class="fs-7 text-muted fw-semibold">Nilai inventori dari pembelian tunai
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column mt-7">
                                        <span class="fs-2x fw-bold text-dark me-2 lh-1 ls-n2">Rp
                                            {{ number_format($totalAssetsTunai, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assets Expired Card -->
                        <div class="col-xl-6">
                            <div class="card card-xl-stretch mb-xl-8 bg-light-danger">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-40px me-3">
                                            <div class="symbol-label bg-danger">
                                                <i class="ki-duotone ki-trash-square text-inverse-danger fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-5 text-dark fw-bold">Obat Expired</div>
                                            <div class="fs-7 text-muted fw-semibold">Nilai inventori obat kadaluarsa</div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column mt-7">
                                        <span class="fs-2x fw-bold text-dark me-2 lh-1 ls-n2">Rp
                                            {{ number_format($totalAssetsExpired, 0, ',', '.') }}</span>
                                        <span
                                            class="fs-6 fw-semibold text-muted mt-1">{{ number_format($totalExpiredQty, 0, ',', '.') }}
                                            item expired</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Info Card-->
            <a href="{{ route('obat.index') }}" class="card bg-primary hoverable h-100 mb-5 mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Icon-->
                    <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                        <i class="ki-duotone ki-medicine text-white fs-3x">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Icon-->

                    <!--begin::Section-->
                    <div class="text-center">
                        <h1 class="text-white fw-bold">{{ $totalProducts }}</h1>
                        <div class="text-white fw-semibold fs-3">Total Produk</div>
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Body-->
            </a>
            <!--end::Info Card-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Info Card-->
            <a href="{{ route('obat.index') }}?stock=low" class="card bg-warning hoverable h-100 mb-5 mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Icon-->
                    <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                        <i class="ki-duotone ki-warning-sign text-white fs-3x">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Icon-->

                    <!--begin::Section-->
                    <div class="text-center">
                        <h1 class="text-white fw-bold">{{ $lowStockProducts }}</h1>
                        <div class="text-white fw-semibold fs-3">Stok Menipis</div>
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Body-->
            </a>
            <!--end::Info Card-->
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::Info Card-->
            <a href="{{ route('obat.index') }}?stock=out" class="card bg-danger hoverable h-100 mb-5 mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Icon-->
                    <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                        <i class="ki-duotone ki-cross-square text-white fs-3x">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Icon-->

                    <!--begin::Section-->
                    <div class="text-center">
                        <h1 class="text-white fw-bold">{{ $outOfStockProducts }}</h1>
                        <div class="text-white fw-semibold fs-3">Stok Habis</div>
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Body-->
            </a>
            <!--end::Info Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
    <script>
        // Format numbers with thousand separator
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare sales chart data with defaults - ensure we have valid arrays
            var salesChartLabels = @json($dailySalesChartData['labels'] ?? []);
            var salesChartData = @json($dailySalesChartData['data'] ?? []);

            // Ensure data is properly formatted for ApexCharts
            if (!Array.isArray(salesChartLabels)) salesChartLabels = [];
            if (!Array.isArray(salesChartData)) salesChartData = [];

            // Convert any potential null values to 0
            salesChartData = salesChartData.map(function(value) {
                return value === null ? 0 : Number(value);
            });

            // Only initialize chart if we have the element in DOM
            if (document.getElementById('salesChart')) {
                if (salesChartLabels.length > 0 && salesChartData.length > 0) {
                    // Daily sales chart options
                    var salesChartOptions = {
                        series: [{
                            name: 'Penjualan',
                            data: salesChartData
                        }],
                        chart: {
                            type: 'area',
                            height: 350,
                            fontFamily: 'inherit',
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            }
                        },
                        colors: ['#3E97FF'],
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        xaxis: {
                            categories: salesChartLabels,
                            axisBorder: {
                                show: false,
                            },
                            axisTicks: {
                                show: false
                            },
                            labels: {
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(value) {
                                    return 'Rp ' + formatNumber(value);
                                },
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        legend: {
                            show: false
                        },
                        fill: {
                            opacity: 0.3,
                            type: 'gradient',
                            gradient: {
                                shade: 'light',
                                type: "vertical",
                                shadeIntensity: 0.5,
                                opacityFrom: 0.7,
                                opacityTo: 0.2,
                                stops: [0, 100]
                            }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                            strokeDashArray: 4,
                            yaxis: {
                                lines: {
                                    show: true
                                }
                            }
                        },
                        markers: {
                            size: 4,
                            colors: ['#3E97FF'],
                            strokeColors: '#FFFFFF',
                            strokeWidth: 2,
                            hover: {
                                size: 7
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return 'Rp ' + formatNumber(value);
                                }
                            }
                        }
                    };

                    // Create the sales chart
                    try {
                        var salesChart = new ApexCharts(document.getElementById('salesChart'), salesChartOptions);
                        salesChart.render();
                    } catch (error) {
                        console.error("Error rendering sales chart:", error);
                        document.getElementById('salesChart').innerHTML =
                            '<div class="text-center py-5"><span class="fs-6 text-gray-500">Terjadi kesalahan saat memuat grafik</span></div>';
                    }
                } else {
                    document.getElementById('salesChart').innerHTML =
                        '<div class="text-center py-5"><span class="fs-6 text-gray-500">Tidak ada data penjualan untuk ditampilkan</span></div>';
                }
            }

            // Expense chart initialization
            if (document.getElementById('expenseChart')) {
                // Prepare expense chart data with defaults
                var expenseChartLabels = @json($expenseChartData['labels'] ?? []);
                var expenseChartData = @json($expenseChartData['data'] ?? []);

                // Ensure data is properly formatted for ApexCharts
                if (!Array.isArray(expenseChartLabels)) expenseChartLabels = [];
                if (!Array.isArray(expenseChartData)) expenseChartData = [];

                // Convert any potential null values to 0
                expenseChartData = expenseChartData.map(function(value) {
                    return value === null ? 0 : Number(value);
                });

                if (expenseChartLabels.length > 0 && expenseChartData.length > 0) {
                    // Expense chart (donut) configuration
                    var expenseChartOptions = {
                        series: expenseChartData,
                        chart: {
                            type: 'donut',
                            height: 250,
                            fontFamily: 'inherit',
                            toolbar: {
                                show: false
                            }
                        },
                        labels: expenseChartLabels,
                        colors: ['#3E97FF', '#50CD89', '#F1416C', '#FFC700', '#7239EA'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            showAlways: true,
                                            formatter: function(w) {
                                                const total = w.globals.seriesTotals.reduce((a, b) => a + b,
                                                    0);
                                                return 'Rp ' + formatNumber(total);
                                            },
                                            fontSize: '16px',
                                            fontWeight: 'bold'
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            show: false
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 200
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        tooltip: {
                            y: {
                                formatter: function(value) {
                                    return 'Rp ' + formatNumber(value);
                                }
                            }
                        }
                    };

                    // Create the expense chart
                    try {
                        var expenseChart = new ApexCharts(document.getElementById('expenseChart'),
                            expenseChartOptions);
                        expenseChart.render();
                    } catch (error) {
                        console.error("Error rendering expense chart:", error);
                        document.getElementById('expenseChart').innerHTML =
                            '<div class="text-center py-5"><span class="fs-6 text-gray-500">Terjadi kesalahan saat memuat grafik</span></div>';
                    }
                } else {
                    document.getElementById('expenseChart').innerHTML =
                        '<div class="text-center py-5"><span class="fs-6 text-gray-500">Tidak ada data pengeluaran untuk ditampilkan</span></div>';
                }
            }

            // Format numbers with thousand separator
            function formatNumber(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            // Handle period change for sales chart
            if (document.getElementById('periodSelect')) {
                document.getElementById('periodSelect').addEventListener('change', function() {
                    const period = this.value;

                    // Show a loading indicator
                    if (document.getElementById('salesChart')) {
                        document.getElementById('salesChart').classList.add('opacity-50');
                    }

                    // Get updated data via AJAX
                    fetch(`{{ route('dashboard.data') }}?period=${period}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            try {
                                // Process chart data - ensure we have valid data
                                let chartData = data.chartData.data || [];
                                let chartLabels = data.chartData.labels || [];

                                // Convert any potential null values to 0
                                chartData = chartData.map(function(value) {
                                    return value === null ? 0 : Number(value);
                                });

                                // Update the chart if it exists
                                if (typeof salesChart !== 'undefined' && salesChart) {
                                    // Update series data first
                                    salesChart.updateSeries([{
                                        name: 'Penjualan',
                                        data: chartData
                                    }]);

                                    // Then update axis categories
                                    salesChart.updateOptions({
                                        xaxis: {
                                            categories: chartLabels
                                        }
                                    });
                                }

                                // Remove loading state
                                if (document.getElementById('salesChart')) {
                                    document.getElementById('salesChart').classList.remove(
                                        'opacity-50');
                                }

                                // Update summary values
                                if (document.getElementById('chartTotalSales')) {
                                    document.getElementById('chartTotalSales').textContent = data
                                        .totalSales;
                                }
                                if (document.getElementById('chartTotalExpenses')) {
                                    document.getElementById('chartTotalExpenses').textContent = data
                                        .totalExpenses;
                                }
                                if (document.getElementById('chartNetProfit')) {
                                    document.getElementById('chartNetProfit').textContent = data
                                        .netProfit;
                                }
                            } catch (updateError) {
                                console.error("Error updating chart:", updateError);

                                // Show error message if update fails
                                if (document.getElementById('salesChart')) {
                                    document.getElementById('salesChart').classList.remove(
                                        'opacity-50');
                                    document.getElementById('salesChart').innerHTML =
                                        '<div class="text-center py-5"><span class="fs-6 text-gray-500">Terjadi kesalahan saat memperbarui grafik</span></div>';
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching chart data:', error);

                            // Show error message if fetch fails
                            if (document.getElementById('salesChart')) {
                                document.getElementById('salesChart').classList.remove('opacity-50');
                                document.getElementById('salesChart').innerHTML =
                                    '<div class="text-center py-5"><span class="fs-6 text-gray-500">Gagal memuat data grafik</span></div>';
                            }
                        });
                });
            }
        });
    </script>
@endpush
