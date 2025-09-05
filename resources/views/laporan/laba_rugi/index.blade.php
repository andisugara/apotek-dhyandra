@extends('layout.app')
@section('title', 'Laporan Laba Rugi')

@section('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .profit-positive {
            color: #50CD89;
        }

        .profit-negative {
            color: #F1416C;
        }

        .financial-summary .value {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .financial-summary .label {
            font-size: 0.9rem;
            color: #7E8299;
        }

        .key-metric {
            background-color: #F1FAFF;
            padding: 1.5rem;
            border-radius: 0.475rem;
            height: 100%;
        }

        .key-metric .number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .key-metric .title {
            color: #7E8299;
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
                <h3 class="fw-bold">Laporan Laba Rugi</h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body py-4">
            <form action="{{ route('laporan.laba-rugi.index') }}" method="GET" id="report-form">
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

                        @if (isset($netProfit))
                            <a href="{{ route('laporan.laba-rugi.pdf', ['startDate' => $startDate, 'endDate' => $endDate]) }}"
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

            @if (isset($netProfit))
                <!-- Begin::Financial Summary -->
                <div class="row g-5 g-xl-8 mb-8">
                    <div class="col-xl-4">
                        <div class="key-metric">
                            <div class="d-flex flex-center h-50px w-50px mb-5 bg-light-primary rounded">
                                <i class="ki-duotone ki-dollar text-primary fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="number text-gray-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                            <div class="title">Total Pendapatan</div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="key-metric">
                            <div class="d-flex flex-center h-50px w-50px mb-5 bg-light-danger rounded">
                                <i class="ki-duotone ki-tag text-danger fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="number text-gray-900">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</div>
                            <div class="title">Total Pengeluaran</div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="key-metric">
                            <div
                                class="d-flex flex-center h-50px w-50px mb-5 bg-light-{{ $netProfit > 0 ? 'success' : 'danger' }} rounded">
                                <i
                                    class="ki-duotone ki-chart-line {{ $netProfit > 0 ? 'text-success' : 'text-danger' }} fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                            <div class="number {{ $netProfit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                Rp {{ number_format($netProfit, 0, ',', '.') }}
                            </div>
                            <div class="title">Laba Bersih</div>
                        </div>
                    </div>
                </div>
                <!-- End::Financial Summary -->

                <!-- Begin::Financial Metrics -->
                <div class="row g-5 g-xl-8 mb-8">
                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold text-gray-900">Margin Laba Kotor</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-duotone ki-chart-pie-simple text-success fs-2x">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <span
                                            class="text-gray-900 fw-bold fs-2x mb-1">{{ number_format($grossProfitMargin, 2) }}%</span>
                                        <span class="text-gray-600 fw-semibold fs-7">Pendapatan: Rp
                                            {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                                        <span class="text-gray-600 fw-semibold fs-7">HPP: Rp
                                            {{ number_format($costOfGoodsSold, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold text-gray-900">Margin Laba Bersih</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <span
                                            class="symbol-label bg-light-{{ $netProfitMargin > 15 ? 'success' : ($netProfitMargin > 0 ? 'warning' : 'danger') }}">
                                            <i
                                                class="ki-duotone ki-chart-line-star text-{{ $netProfitMargin > 15 ? 'success' : ($netProfitMargin > 0 ? 'warning' : 'danger') }} fs-2x">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <span
                                            class="text-gray-900 fw-bold fs-2x mb-1">{{ number_format($netProfitMargin, 2) }}%</span>
                                        <span class="text-gray-600 fw-semibold fs-7">Laba Kotor: Rp
                                            {{ number_format($grossProfit, 0, ',', '.') }}</span>
                                        <span class="text-gray-600 fw-semibold fs-7">Biaya: Rp
                                            {{ number_format($totalExpenses, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0">
                                <h3 class="card-title fw-bold text-gray-900">Rasio Biaya Terhadap Pendapatan</h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-5">
                                        <span
                                            class="symbol-label bg-light-{{ $expenseRatio > 80 ? 'danger' : ($expenseRatio > 60 ? 'warning' : 'success') }}">
                                            <i
                                                class="ki-duotone ki-percentage text-{{ $expenseRatio > 80 ? 'danger' : ($expenseRatio > 60 ? 'warning' : 'success') }} fs-2x">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <span
                                            class="text-gray-900 fw-bold fs-2x mb-1">{{ number_format($expenseRatio, 2) }}%</span>
                                        <span class="text-gray-600 fw-semibold fs-7">Pengeluaran sebagai persentase dari
                                            pendapatan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End::Financial Metrics -->

                <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                    <div class="col-xl-8">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Tren Laba Bersih</span>
                                    <span class="text-muted fw-semibold fs-7">6 bulan terakhir</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="profit_chart" style="height: 350px"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card card-xl-stretch mb-xl-8">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Kategori Pengeluaran</span>
                                    <span class="text-muted fw-semibold fs-7">Dalam periode terpilih</span>
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <div id="expense_chart" style="height: 300px"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Begin::Income Statement -->
                <div class="card mb-8">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Laporan Laba Rugi</span>
                            <span class="text-muted fw-semibold fs-7">Periode:
                                {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                        </h3>
                    </div>
                    <div class="card-body py-5">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <!-- Pendapatan -->
                                <tr class="fw-bold fs-5">
                                    <td colspan="2">Pendapatan</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 2rem">Penjualan</td>
                                    <td class="text-end">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total Pendapatan</td>
                                    <td class="text-end border-top">Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>

                                <!-- Harga Pokok Penjualan -->
                                <tr class="fw-bold fs-5">
                                    <td colspan="2">Harga Pokok Penjualan (HPP)</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 2rem">Harga Pokok Penjualan Obat</td>
                                    <td class="text-end">Rp {{ number_format($costOfGoodsSold, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total HPP</td>
                                    <td class="text-end border-top">Rp {{ number_format($costOfGoodsSold, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr class="fw-bold bg-light">
                                    <td>Laba Kotor</td>
                                    <td class="text-end">Rp {{ number_format($grossProfit, 0, ',', '.') }}</td>
                                </tr>

                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>

                                <!-- Pengeluaran -->
                                <tr class="fw-bold fs-5">
                                    <td colspan="2">Pengeluaran</td>
                                </tr>
                                @if ($expenses->count() > 0)
                                    @foreach ($expenses as $expense)
                                        <tr>
                                            <td style="padding-left: 2rem">{{ $expense->nama }}</td>
                                            <td class="text-end">Rp {{ number_format($expense->jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td style="padding-left: 2rem" colspan="2">Tidak ada pengeluaran dalam periode
                                            ini</td>
                                    </tr>
                                @endif
                                <tr class="fw-bold">
                                    <td>Total Pengeluaran</td>
                                    <td class="text-end border-top">Rp {{ number_format($totalExpenses, 0, ',', '.') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>

                                <!-- Laba Bersih -->
                                <tr class="fw-bold fs-5 bg-light">
                                    <td>Laba Bersih</td>
                                    <td class="text-end {{ $netProfit > 0 ? 'profit-positive' : 'profit-negative' }}">
                                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End::Income Statement -->

                <!-- Begin::Expenses Detail -->
                <div class="card mb-5">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Detail Pengeluaran</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4"
                                id="expenses_table">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th>Nama Pengeluaran</th>
                                        <th>Tanggal</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                        <tr>
                                            <td>{{ $expense->nama }}</td>
                                            <td>{{ \Carbon\Carbon::parse($expense->tanggal)->format('d/m/Y') }}</td>
                                            <td class="text-end">Rp {{ number_format($expense->jumlah, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data pengeluaran</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="2" class="text-end">Total</td>
                                        <td class="text-end">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End::Expenses Detail -->
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
                $('#expenses_table').DataTable({
                    order: [
                        [1, 'desc']
                    ],
                    pageLength: 10,
                    responsive: true
                });

                // Initialize profit chart
                var profitChartOptions = {
                    series: [{
                        name: 'Laba Bersih',
                        data: @json($chartData['profit'])
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#50CD89'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                            columnWidth: '60%',
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: @json($chartData['months']),
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
                    fill: {
                        opacity: 1
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
                    if (document.getElementById('profit_chart')) {
                        var profitChart = new ApexCharts(document.getElementById('profit_chart'), profitChartOptions);
                        profitChart.render();
                    }
                } catch (error) {
                    console.error("Error rendering profit chart:", error);
                }

                // Initialize expense chart
                var expenseChartOptions = {
                    series: @json($chartData['expenseValues']),
                    chart: {
                        type: 'donut',
                        height: 300,
                        toolbar: {
                            show: false
                        }
                    },
                    labels: @json($chartData['expenseLabels']),
                    colors: ['#3E97FF', '#F1416C', '#50CD89', '#FFC700', '#7239EA'],
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
                                            const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
                        position: 'bottom'
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

                try {
                    if (document.getElementById('expense_chart') && expenseChartOptions.series && expenseChartOptions
                        .series.length > 0) {
                        var expenseChart = new ApexCharts(document.getElementById('expense_chart'),
                        expenseChartOptions);
                        expenseChart.render();
                    } else if (document.getElementById('expense_chart')) {
                        document.getElementById('expense_chart').innerHTML =
                            '<div class="text-center py-5"><span class="fs-6 text-gray-500">Tidak ada data pengeluaran untuk ditampilkan</span></div>';
                    }
                } catch (error) {
                    console.error("Error rendering expense chart:", error);
                }

                function formatNumber(number) {
                    return new Intl.NumberFormat('id-ID').format(number);
                }
            });
        </script>
    @endif
@endpush
