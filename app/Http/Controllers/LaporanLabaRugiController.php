<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanLabaRugiController extends Controller
{
    /**
     * Display the profit and loss report page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Default: show current month
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        return view('laporan.laba_rugi.index', compact('startDate', 'endDate'));
    }

    /**
     * Generate profit and loss report based on date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Sales revenue and cost of goods sold
        $salesData = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->join('obat_satuan', function ($join) {
                $join->on('obat.id', '=', 'obat_satuan.obat_id')
                    ->on('penjualan_details.satuan_id', '=', 'obat_satuan.satuan_id');
            })
            ->select(
                DB::raw('SUM(penjualan_details.total) as total_sales'),
                DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_cost')
            )
            ->whereBetween('penjualan.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->first();

        $totalRevenue = $salesData->total_sales ?? 0;
        $costOfGoodsSold = $salesData->total_cost ?? 0;
        $grossProfit = $totalRevenue - $costOfGoodsSold;

        // Expenses
        $expenses = Pengeluaran::select(
            'nama',
            'jumlah'
        )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $totalExpenses = $expenses->sum('jumlah');
        $netProfit = $grossProfit - $totalExpenses;

        // Monthly data for chart
        $months = [];
        $profitData = [];

        // Get 6 months of data for chart
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::parse($endDate)->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::parse($endDate)->subMonths($i)->endOfMonth();

            // Sales for this month
            $monthlySales = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
                ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
                ->join('obat_satuan', function ($join) {
                    $join->on('obat.id', '=', 'obat_satuan.obat_id')
                        ->on('penjualan_details.satuan_id', '=', 'obat_satuan.satuan_id');
                })
                ->select(
                    DB::raw('SUM(penjualan_details.total) as total_sales'),
                    DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_cost')
                )
                ->whereBetween('penjualans.tanggal_penjualan', [$monthStart, $monthEnd])
                ->first();

            // Expenses for this month
            $monthlyExpenses = Pengeluaran::whereBetween('tanggal', [$monthStart, $monthEnd])
                ->sum('jumlah');

            $monthlyGrossProfit = ($monthlySales->total_sales ?? 0) - ($monthlySales->total_cost ?? 0);
            $monthlyNetProfit = $monthlyGrossProfit - $monthlyExpenses;

            $months[] = $monthStart->format('M Y');
            $profitData[] = $monthlyNetProfit;
        }

        // Financial ratios
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;

        // Expense categories
        $expenseCategories = DB::table('pengeluaran')
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(jumlah) as total'),
                DB::raw("SUBSTRING_INDEX(nama, ' ', 1) as category")
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Chart data
        $chartData = [
            'months' => $months,
            'profit' => $profitData,
            'expenseLabels' => $expenseCategories->pluck('category')->toArray(),
            'expenseValues' => $expenseCategories->pluck('total')->toArray(),
        ];

        return view('laporan.laba_rugi.index', compact(
            'startDate',
            'endDate',
            'totalRevenue',
            'costOfGoodsSold',
            'grossProfit',
            'expenses',
            'totalExpenses',
            'netProfit',
            'grossProfitMargin',
            'netProfitMargin',
            'expenseRatio',
            'chartData'
        ));
    }

    /**
     * Export profit and loss report to PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Sales revenue and cost of goods sold
        $salesData = PenjualanDetail::join('penjualan', 'penjualan.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->join('obat_satuan', function ($join) {
                $join->on('obat.id', '=', 'obat_satuan.obat_id')
                    ->on('penjualan_details.satuan_id', '=', 'obat_satuan.satuan_id');
            })
            ->select(
                DB::raw('SUM(penjualan_details.total) as total_sales'),
                DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_cost')
            )
            ->whereBetween('penjualan.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->first();

        $totalRevenue = $salesData->total_sales ?? 0;
        $costOfGoodsSold = $salesData->total_cost ?? 0;
        $grossProfit = $totalRevenue - $costOfGoodsSold;

        // Expenses
        $expenses = Pengeluaran::select(
            'nama',
            'tanggal',
            'jumlah'
        )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $totalExpenses = $expenses->sum('jumlah');
        $netProfit = $grossProfit - $totalExpenses;

        // Financial ratios
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        $expenseRatio = $totalRevenue > 0 ? ($totalExpenses / $totalRevenue) * 100 : 0;

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('laporan.laba_rugi.pdf', compact(
            'startDate',
            'endDate',
            'totalRevenue',
            'costOfGoodsSold',
            'grossProfit',
            'expenses',
            'totalExpenses',
            'netProfit',
            'grossProfitMargin',
            'netProfitMargin',
            'expenseRatio'
        ));

        return $pdf->download('laporan-laba-rugi-' . $startDate . '-' . $endDate . '.pdf');
    }
}
