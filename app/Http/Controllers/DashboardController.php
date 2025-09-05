<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pengeluaran;
use App\Models\Obat;
use App\Models\TransaksiAkun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with summary data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get today's date
        $today = Carbon::today();
        $firstDayOfMonth = Carbon::today()->startOfMonth();
        $lastDayOfMonth = Carbon::today()->endOfMonth();
        $yesterday = Carbon::yesterday();
        $firstDayOfLastMonth = Carbon::today()->subMonth()->startOfMonth();
        $lastDayOfLastMonth = Carbon::today()->subMonth()->endOfMonth();

        // Today's transactions
        $todayTransactionsCount = Penjualan::whereDate('tanggal_penjualan', $today)->count();
        $todayTotalSales = Penjualan::whereDate('tanggal_penjualan', $today)->sum('grand_total');
        $todayAverageSale = $todayTransactionsCount > 0
            ? $todayTotalSales / $todayTransactionsCount
            : 0;

        // Yesterday's transactions (for comparison)
        $yesterdayTotalSales = Penjualan::whereDate('tanggal_penjualan', $yesterday)->sum('grand_total');
        $salesGrowth = $yesterdayTotalSales > 0
            ? (($todayTotalSales - $yesterdayTotalSales) / $yesterdayTotalSales) * 100
            : ($todayTotalSales > 0 ? 100 : 0);

        // This month's summary
        $monthlyTotalSales = Penjualan::whereBetween('tanggal_penjualan', [$firstDayOfMonth, $lastDayOfMonth])->sum('grand_total');
        $monthlyTotalExpenses = Pengeluaran::whereBetween('tanggal', [$firstDayOfMonth, $lastDayOfMonth])->sum('jumlah');
        $monthlyNetProfit = $monthlyTotalSales - $monthlyTotalExpenses;

        // Last month's summary (for comparison)
        $lastMonthTotalSales = Penjualan::whereBetween('tanggal_penjualan', [$firstDayOfLastMonth, $lastDayOfLastMonth])->sum('grand_total');
        $lastMonthTotalExpenses = Pengeluaran::whereBetween('tanggal', [$firstDayOfLastMonth, $lastDayOfLastMonth])->sum('jumlah');
        $lastMonthNetProfit = $lastMonthTotalSales - $lastMonthTotalExpenses;

        $monthlyProfitGrowth = $lastMonthNetProfit > 0
            ? (($monthlyNetProfit - $lastMonthNetProfit) / $lastMonthNetProfit) * 100
            : ($monthlyNetProfit > 0 ? 100 : 0);

        // Inventory summary
        $totalProducts = Obat::count();
        $lowStockProducts = Obat::whereHas('stok', function ($query) {
            $query->select('obat_id')
                ->selectRaw('SUM(qty) as total_qty')
                ->groupBy('obat_id')
                ->having('total_qty', '<', 10);
        })->count();

        $outOfStockProducts = Obat::whereDoesntHave('stok', function ($query) {
            $query->where('qty', '>', 0);
        })->count();

        // Sales by day of current month (for chart)
        $dailySalesData = Penjualan::select(
            DB::raw('DATE(tanggal_penjualan) as date'),
            DB::raw('SUM(grand_total) as total_sales')
        )
            ->whereBetween('tanggal_penjualan', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailySalesChartData = [
            'labels' => $dailySalesData->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'data' => $dailySalesData->pluck('total_sales')->toArray()
        ];

        // Top selling products this month
        $topProducts = DB::table('penjualan_details')
            ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->select(
                'obat.id',
                'obat.nama_obat',
                DB::raw('SUM(penjualan_details.jumlah) as total_qty'),
                DB::raw('SUM(penjualan_details.total) as total_sales')
            )
            ->whereBetween('penjualans.tanggal_penjualan', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('obat.id', 'obat.nama_obat')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        // Expense categories this month
        $expensesByCategory = TransaksiAkun::join('akun', 'akun.id', '=', 'transaksi_akun.akun_id')
            ->select(
                'akun.id',
                'akun.nama',
                DB::raw('SUM(transaksi_akun.debit) as total_amount')
            )
            ->where('transaksi_akun.tipe_referensi', 'PENGELUARAN')
            ->whereBetween('transaksi_akun.tanggal', [$firstDayOfMonth, $lastDayOfMonth])
            ->groupBy('akun.id', 'akun.nama')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $expenseChartData = [
            'labels' => $expensesByCategory->pluck('nama')->toArray(),
            'data' => $expensesByCategory->pluck('total_amount')->toArray()
        ];

        // Recent transactions
        $recentTransactions = Penjualan::with(['pasien', 'user'])
            ->orderByDesc('tanggal_penjualan')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todayTransactionsCount',
            'todayTotalSales',
            'todayAverageSale',
            'salesGrowth',
            'monthlyTotalSales',
            'monthlyTotalExpenses',
            'monthlyNetProfit',
            'monthlyProfitGrowth',
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'dailySalesChartData',
            'topProducts',
            'expenseChartData',
            'expensesByCategory',
            'recentTransactions'
        ));
    }

    /**
     * Get JSON data for AJAX requests to update dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpdatedData(Request $request)
    {
        $period = $request->input('period', 'today');

        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->subWeek()->startOfDay();
                $endDate = Carbon::now();
                break;

            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
                break;

            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now();
                break;

            case 'today':
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::now();
                break;
        }

        // Get sales data for the selected period
        $totalSales = Penjualan::whereBetween('tanggal_penjualan', [$startDate, $endDate])
            ->sum('grand_total');

        $totalExpenses = Pengeluaran::whereBetween('tanggal', [$startDate, $endDate])
            ->sum('jumlah');

        $netProfit = $totalSales - $totalExpenses;

        // Get chart data
        if ($period == 'today') {
            // For today, group by hour
            $salesChartData = Penjualan::select(
                DB::raw('HOUR(tanggal_penjualan) as hour'),
                DB::raw('SUM(grand_total) as total_sales')
            )
                ->whereDate('tanggal_penjualan', Carbon::today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $labels = [];
            $data = array_fill(0, 24, 0);

            foreach ($salesChartData as $item) {
                $data[$item->hour] = $item->total_sales;
            }

            for ($i = 0; $i < 24; $i++) {
                $labels[] = sprintf('%02d:00', $i);
            }
        } else if ($period == 'week') {
            // For week, group by day
            $salesChartData = Penjualan::select(
                DB::raw('DATE(tanggal_penjualan) as date'),
                DB::raw('SUM(grand_total) as total_sales')
            )
                ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $data = [];

            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::now()->subDays(6 - $i);
                $dateStr = $date->toDateString();
                $labels[] = $date->format('D');

                $sale = $salesChartData->firstWhere('date', $dateStr);
                $data[] = $sale ? $sale->total_sales : 0;
            }
        } else if ($period == 'month') {
            // For month, group by day
            $salesChartData = Penjualan::select(
                DB::raw('DATE(tanggal_penjualan) as date'),
                DB::raw('SUM(grand_total) as total_sales')
            )
                ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $data = [];

            $daysInMonth = $startDate->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = Carbon::create($startDate->year, $startDate->month, $i);
                if ($date > Carbon::now()) break;

                $dateStr = $date->toDateString();
                $labels[] = $date->format('d M');

                $sale = $salesChartData->firstWhere('date', $dateStr);
                $data[] = $sale ? $sale->total_sales : 0;
            }
        } else {
            // For year, group by month
            $salesChartData = Penjualan::select(
                DB::raw('MONTH(tanggal_penjualan) as month'),
                DB::raw('SUM(grand_total) as total_sales')
            )
                ->whereYear('tanggal_penjualan', Carbon::now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $labels = [];
            $data = array_fill(0, 12, 0);

            foreach ($salesChartData as $item) {
                $data[$item->month - 1] = $item->total_sales;
            }

            for ($i = 0; $i < 12; $i++) {
                $labels[] = Carbon::create(null, $i + 1, 1)->format('M');
            }
        }

        return response()->json([
            'totalSales' => number_format($totalSales, 0, ',', '.'),
            'totalExpenses' => number_format($totalExpenses, 0, ',', '.'),
            'netProfit' => number_format($netProfit, 0, ',', '.'),
            'chartData' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }
}
