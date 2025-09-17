<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Obat;
use App\Models\SatuanObat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LaporanPenjualanController extends Controller
{
    /**
     * Display the sales report page with date filter.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Default: show current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        if ($request->ajax()) {
            // Get detailed sales data with profit calculation
            $query = PenjualanDetail::with(['obat', 'satuan', 'penjualan'])
                ->join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
                ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
                ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'penjualan_details.satuan_id')
                ->select(
                    'penjualan_details.id',
                    'penjualans.no_faktur',
                    'penjualans.tanggal_penjualan',
                    'obat.nama_obat',
                    'satuan_obat.nama as satuan',
                    'penjualan_details.harga_beli',
                    'penjualan_details.harga',
                    'penjualan_details.jumlah',
                    'penjualan_details.diskon',
                    'penjualan_details.ppn',
                    'penjualan_details.total',
                    DB::raw('((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as keuntungan')
                )
                ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('satuan', function ($row) {
                    return $row->satuan ?? '-';
                })
                ->addColumn('tanggal_formatted', function ($row) {
                    return Carbon::parse($row->tanggal_penjualan)->format('d/m/Y H:i');
                })
                ->addColumn('harga_beli_formatted', function ($row) {
                    return 'Rp ' . number_format($row->harga_beli, 0, ',', '.');
                })
                ->addColumn('harga_jual_formatted', function ($row) {
                    return 'Rp ' . number_format($row->harga, 0, ',', '.');
                })
                ->addColumn('diskon_formatted', function ($row) {
                    return 'Rp ' . number_format($row->diskon, 0, ',', '.');
                })
                ->addColumn('ppn_formatted', function ($row) {
                    return 'Rp ' . number_format($row->ppn, 0, ',', '.');
                })
                ->addColumn('total_formatted', function ($row) {
                    return 'Rp ' . number_format($row->total, 0, ',', '.');
                })
                ->addColumn('keuntungan_formatted', function ($row) {
                    $class = $row->keuntungan >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' . $class . '">Rp ' . number_format($row->keuntungan, 0, ',', '.') . '</span>';
                })
                ->rawColumns(['keuntungan_formatted'])
                ->make(true);
        }

        // Get summary data for the selected period
        $summary = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->select(
                DB::raw('SUM(penjualan_details.total) as total_penjualan'),
                DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_hpp'),
                DB::raw('SUM((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as total_keuntungan')
            )
            ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->first();

        return view('laporan.penjualan.index', compact('startDate', 'endDate', 'summary'));
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

        // Get detailed sales data with profit calculation
        $salesData = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'penjualan_details.satuan_id')
            ->select(
                'penjualans.no_faktur',
                'penjualans.tanggal_penjualan',
                'obat.nama_obat',
                'satuan_obat.nama as satuan',
                'penjualan_details.harga_beli',
                'penjualan_details.harga',
                'penjualan_details.jumlah',
                'penjualan_details.diskon',
                'penjualan_details.ppn',
                'penjualan_details.total',
                DB::raw('((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as keuntungan')
            )
            ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('penjualans.tanggal_penjualan', 'desc')
            ->get();

        // Calculate summary
        $totalPenjualan = $salesData->sum('total');
        $totalHPP = $salesData->sum(function ($item) {
            return $item->harga_beli * $item->jumlah;
        });
        $totalKeuntungan = $salesData->sum('keuntungan');

        $summary = [
            'total_penjualan' => $totalPenjualan,
            'total_hpp' => $totalHPP,
            'total_keuntungan' => $totalKeuntungan,
        ];

        return view('laporan.penjualan.index', compact(
            'startDate',
            'endDate',
            'salesData',
            'summary'
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
        $salesData = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'penjualan_details.satuan_id')
            ->select(
                'penjualans.no_faktur',
                'penjualans.tanggal_penjualan',
                'obat.nama_obat',
                'satuan_obat.nama as satuan',
                'penjualan_details.harga_beli',
                'penjualan_details.harga',
                'penjualan_details.jumlah',
                'penjualan_details.diskon',
                'penjualan_details.ppn',
                'penjualan_details.total',
                DB::raw('((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as keuntungan')
            )
            ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('penjualans.tanggal_penjualan', 'desc')
            ->get();

        // Calculate summary
        $totalPenjualan = $salesData->sum('total');
        $totalHPP = $salesData->sum(function ($item) {
            return $item->harga_beli * $item->jumlah;
        });
        $totalKeuntungan = $salesData->sum('keuntungan');

        $summary = [
            'total_penjualan' => $totalPenjualan,
            'total_hpp' => $totalHPP,
            'total_keuntungan' => $totalKeuntungan,
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('laporan.penjualan.pdf', compact(
            'startDate',
            'endDate',
            'salesData',
            'summary'
        ));

        return $pdf->download('laporan-penjualan-' . $startDate . '-' . $endDate . '.pdf');
    }
}
