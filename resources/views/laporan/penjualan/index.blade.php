@extends('layout.app')
@section('title', 'Laporan Penjualan')

@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .profit-positive {
            color: #50CD89;
        }

        .profit-negative {
            color: #F1416C;
        }
    </style>
@endsection

@section('content')
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h3 class="fw-bold">Laporan Penjualan</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <form action="{{ route('laporan.penjualan.index') }}" method="GET" id="report-form">
                <div class="row mb-8">
                    <div class="col-md-4">
                        <label class="required form-label">Tanggal Awal</label>
                        <input type="date" class="form-control form-control-solid" name="start_date"
                            value="{{ $startDate }}" required />
                    </div>

                    <div class="col-md-4">
                        <label class="required form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-solid" name="end_date"
                            value="{{ $endDate }}" required />
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-3">
                            <i class="ki-duotone ki-filter fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Filter
                        </button>

                        @if (isset($salesDetails) && $salesDetails->count() > 0)
                            <a href="{{ route('laporan.penjualan.pdf', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
                                class="btn btn-danger" target="_blank">
                                <i class="ki-duotone ki-file-down fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Export PDF
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            @if (isset($salesSummary))
                <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                    <div class="col-xl-4">
                        <div class="card bg-light-primary hoverable h-100 mb-5 mb-xl-8">
                            <div class="card-body">
                                <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                                    <i class="ki-duotone ki-tag-user text-primary fs-3x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                                <div class="text-center">
                                    <h1 class="fw-bold">{{ number_format($salesSummary->total_transactions, 0, ',', '.') }}
                                    </h1>
                                    <div class="fs-3 fw-semibold">Total Transaksi</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card bg-light-success hoverable h-100 mb-5 mb-xl-8">
                            <div class="card-body">
                                <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                                    <i class="ki-duotone ki-dollar text-success fs-3x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                                <div class="text-center">
                                    <h1 class="fw-bold">Rp {{ number_format($salesSummary->total_revenue, 0, ',', '.') }}
                                    </h1>
                                    <div class="fs-3 fw-semibold">Total Pendapatan</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card bg-light-warning hoverable h-100 mb-5 mb-xl-8">
                            <div class="card-body">
                                <div class="d-flex flex-center h-80px w-80px mb-5 mx-auto">
                                    <i class="ki-duotone ki-chart-line-star text-warning fs-3x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                                <div class="text-center">
                                    <h1 class="fw-bold">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h1>
                                    <div class="fs-3 fw-semibold">Total Keuntungan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                    <div class="col-xl-8">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Grafik Penjualan</span>
                                    <span class="text-muted fw-semibold fs-7">Penjualan harian dalam periode terpilih</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="sales_chart" style="height: 350px"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Produk Terlaris</span>
                                    <span class="text-muted fw-semibold fs-7">Dalam periode terpilih</span>
                                </h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th class="min-w-150px">Produk</th>
                                                <th class="min-w-100px">Terjual</th>
                                                <th class="min-w-100px text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($productSales as $product)
                                                <tr>
                                                    <td>
                                                        <span
                                                            class="text-gray-800 fw-bold text-hover-primary fs-6">{{ $product->nama_obat }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="text-gray-600 fw-semibold d-block fs-7">{{ $product->total_qty }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <span class="text-gray-800 fw-bold d-block fs-6">Rp
                                                            {{ number_format($product->total_sales, 0, ',', '.') }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">Tidak ada data</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-8">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Detail Transaksi</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
                                id="sales_table">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>No. Faktur</th>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th class="text-end">Harga Beli</th>
                                        <th class="text-end">Harga Jual</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Diskon</th>
                                        <th class="text-end">PPN</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Keuntungan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salesDetails as $detail)
                                        <tr>
                                            <td>{{ $detail->no_faktur }}</td>
                                            <td>{{ \Carbon\Carbon::parse($detail->tanggal_penjualan)->format('d/m/Y H:i') }}
                                            </td>
                                            <td>{{ $detail->nama_obat }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ $detail->jumlah }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->diskon, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->ppn, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                                            <td
                                                class="text-end {{ $detail->profit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                                Rp {{ number_format($detail->profit, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="8" class="text-end">Total</td>
                                        <td class="text-end">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
                                        <td
                                            class="text-end {{ $totalProfit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                            Rp {{ number_format($totalProfit, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="8" class="text-end">Harga Pokok Penjualan (HPP)</td>
                                        <td class="text-end" colspan="2">Rp
                                            {{ number_format($totalCost, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td colspan="8" class="text-end">Margin Keuntungan</td>
                                        <td class="text-end {{ $totalProfit > 0 ? 'profit-positive' : 'profit-negative' }}"
                                            colspan="2">
                                            {{ $totalSales > 0 ? number_format(($totalProfit / $totalSales) * 100, 2) : 0 }}%
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @if (isset($chartData))
        <script>
            $(document).ready(function() {
                // Initialize datatable
                $('#sales_table').DataTable({
                    order: [
                        [1, 'desc']
                    ],
                    pageLength: 25,
                    responsive: true
                });

                // Initialize sales chart
                var salesChartOptions = {
                    series: [{
                        name: 'Penjualan',
                        data: @json($chartData['data'])
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
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
                        categories: @json($chartData['labels']),
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

                try {
                    if (document.getElementById('sales_chart')) {
                        var salesChart = new ApexCharts(document.getElementById('sales_chart'), salesChartOptions);
                        salesChart.render();
                    }
                } catch (error) {
                    console.error("Error rendering sales chart:", error);
                }

                function formatNumber(number) {
                    return new Intl.NumberFormat('id-ID').format(number);
                }
            });
        </script>
    @endif
@endpush
