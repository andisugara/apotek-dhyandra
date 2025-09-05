<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanController extends Controller
{
    /**
     * Display the sales report page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Default: show current month
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        return view('laporan.penjualan.index', compact('startDate', 'endDate'));
    }

    /**
     * Generate sales report based on date range.
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

        // Get sales summary
        $salesSummary = Penjualan::select(
            DB::raw('COUNT(*) as total_transactions'),
            DB::raw('SUM(grand_total) as total_revenue'),
            DB::raw('AVG(grand_total) as average_transaction')
        )
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->first();

        // Get detailed sales data with profit calculation
        $salesDetails = PenjualanDetail::join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_detail.obat_id')
            ->join('obat_satuan', function ($join) {
                $join->on('obat.id', '=', 'obat_satuan.obat_id')
                    ->on('penjualan_detail.satuan_id', '=', 'obat_satuan.satuan_id');
            })
            ->select(
                'penjualan.no_faktur',
                'penjualan.tanggal_penjualan',
                'obat.nama_obat',
                'penjualan_detail.harga',
                'obat_satuan.harga_beli',
                'penjualan_detail.jumlah',
                'penjualan_detail.diskon',
                'penjualan_detail.ppn',
                'penjualan_detail.total',
                DB::raw('(penjualan_detail.harga - obat_satuan.harga_beli) * penjualan_detail.jumlah as profit_raw'),
                DB::raw('((penjualan_detail.harga * penjualan_detail.jumlah) - (penjualan_detail.diskon) + (penjualan_detail.ppn) - (obat_satuan.harga_beli * penjualan_detail.jumlah)) as profit')
            )
            ->whereBetween('penjualan.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('penjualan.tanggal_penjualan', 'desc')
            ->get();

        // Calculate total sales, cost and profit
        $totalSales = $salesDetails->sum('total');
        $totalCost = $salesDetails->sum(function ($item) {
            return $item->harga_beli * $item->jumlah;
        });
        $totalProfit = $salesDetails->sum('profit');

        // Daily sales chart data
        $dailySales = Penjualan::select(
            DB::raw('DATE(tanggal_penjualan) as date'),
            DB::raw('SUM(grand_total) as total_sales')
        )
            ->whereBetween('tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartData = [
            'labels' => $dailySales->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d M');
            })->toArray(),
            'data' => $dailySales->pluck('total_sales')->toArray()
        ];

        // Group by product
        $productSales = PenjualanDetail::join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_detail.obat_id')
            ->select(
                'obat.id',
                'obat.nama_obat',
                DB::raw('SUM(penjualan_detail.jumlah) as total_qty'),
                DB::raw('SUM(penjualan_detail.total) as total_sales')
            )
            ->whereBetween('penjualan.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('obat.id', 'obat.nama_obat')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return view('laporan.penjualan.index', compact(
            'startDate',
            'endDate',
            'salesSummary',
            'salesDetails',
            'totalSales',
            'totalCost',
            'totalProfit',
            'chartData',
            'productSales'
        ));
    }

    /**
     * Export sales report to PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get detailed sales data with profit calculation
        $salesDetails = PenjualanDetail::join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_detail.obat_id')
            ->join('obat_satuan', function ($join) {
                $join->on('obat.id', '=', 'obat_satuan.obat_id')
                    ->on('penjualan_detail.satuan_id', '=', 'obat_satuan.satuan_id');
            })
            ->select(
                'penjualan.no_faktur',
                'penjualan.tanggal_penjualan',
                'obat.nama_obat',
                'penjualan_detail.harga',
                'obat_satuan.harga_beli',
                'penjualan_detail.jumlah',
                'penjualan_detail.diskon',
                'penjualan_detail.ppn',
                'penjualan_detail.total',
                DB::raw('((penjualan_detail.harga * penjualan_detail.jumlah) - (penjualan_detail.diskon) + (penjualan_detail.ppn) - (obat_satuan.harga_beli * penjualan_detail.jumlah)) as profit')
            )
            ->whereBetween('penjualan.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('penjualan.tanggal_penjualan', 'desc')
            ->get();

        // Calculate total sales, cost and profit
        $totalSales = $salesDetails->sum('total');
        $totalCost = $salesDetails->sum(function ($item) {
            return $item->harga_beli * $item->jumlah;
        });
        $totalProfit = $salesDetails->sum('profit');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('laporan.penjualan.pdf', compact(
            'startDate',
            'endDate',
            'salesDetails',
            'totalSales',
            'totalCost',
            'totalProfit'
        ));

        return $pdf->download('laporan-penjualan-' . $startDate . '-' . $endDate . '.pdf');
    }
}
