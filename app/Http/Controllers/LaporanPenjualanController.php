<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Obat;
use App\Models\SatuanObat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $jenisPembayaran = $request->input('jenis_pembayaran');

        if ($request->ajax()) {
            if ($request->filled('get_details')) {
                // This is a request for child row details
                $penjualanId = $request->input('penjualan_id');

                $details = PenjualanDetail::where('penjualan_id', $penjualanId)
                    ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
                    ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'penjualan_details.satuan_id')
                    ->select(
                        'penjualan_details.id',
                        'obat.nama_obat',
                        'satuan_obat.nama as satuan',
                        'penjualan_details.harga_beli',
                        'penjualan_details.harga',
                        'penjualan_details.jumlah',
                        'penjualan_details.diskon',
                        'penjualan_details.ppn',
                        'penjualan_details.tuslah',
                        'penjualan_details.embalase',
                        'penjualan_details.total',
                        'penjualan_details.no_batch',
                        'penjualan_details.tanggal_expired',
                        DB::raw('((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as keuntungan')
                    )
                    ->get();

                // Log count for debugging

                return response()->json($details);
            }

            if ($request->filled('summary_only')) {
                // Just return the summary data
                $summaryQuery = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
                    ->select(
                        DB::raw('SUM(penjualan_details.total) as total_penjualan'),
                        DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_hpp'),
                        DB::raw('SUM((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as total_keuntungan')
                    )
                    ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59']);

                // Apply payment type filter if selected
                if (!empty($jenisPembayaran)) {
                    $summaryQuery->where('penjualans.jenis', $jenisPembayaran);
                }

                $summary = $summaryQuery->first();

                return response()->json(['summary' => $summary]);
            }

            // Get parent rows (grouped by penjualan)
            $query = Penjualan::leftJoin('users', 'users.id', '=', 'penjualans.user_id')
                ->leftJoin('pasien', 'pasien.id', '=', 'penjualans.pasien_id')
                ->select(
                    'penjualans.id',
                    'penjualans.no_faktur',
                    'penjualans.tanggal_penjualan',
                    'penjualans.jenis',
                    'pasien.nama as nama_pasien',
                    'penjualans.subtotal',
                    'penjualans.diskon_total',
                    'penjualans.ppn_total',
                    'penjualans.tuslah_total',
                    'penjualans.embalase_total',
                    'penjualans.grand_total',
                    'penjualans.bayar',
                    'penjualans.kembalian',
                    'users.name as user_name',
                    'penjualans.keterangan',
                    DB::raw('(SELECT SUM((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) FROM penjualan_details WHERE penjualan_details.penjualan_id = penjualans.id) as total_keuntungan')
                )
                ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59']);

            // Apply payment type filter if selected
            if (!empty($jenisPembayaran)) {
                $query->where('penjualans.jenis', $jenisPembayaran);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('pasien', function ($row) {
                    return $row->nama_pasien ?? 'Umum';
                })
                ->addColumn('tanggal_formatted', function ($row) {
                    return Carbon::parse($row->tanggal_penjualan)->format('d/m/Y H:i');
                })
                ->addColumn('jenis_formatted', function ($row) {
                    $badge = $row->jenis === 'TUNAI' ? 'badge-light-success' : 'badge-light-primary';
                    return '<span class="badge ' . $badge . '">' . $row->jenis . '</span>';
                })
                ->addColumn('subtotal_formatted', function ($row) {
                    return 'Rp ' . number_format($row->subtotal, 0, ',', '.');
                })
                ->addColumn('diskon_formatted', function ($row) {
                    return 'Rp ' . number_format($row->diskon_total, 0, ',', '.');
                })
                ->addColumn('ppn_formatted', function ($row) {
                    return 'Rp ' . number_format($row->ppn_total, 0, ',', '.');
                })
                ->addColumn('tuslah_formatted', function ($row) {
                    return 'Rp ' . number_format($row->tuslah_total, 0, ',', '.');
                })
                ->addColumn('embalase_formatted', function ($row) {
                    return 'Rp ' . number_format($row->embalase_total, 0, ',', '.');
                })
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . number_format($row->grand_total, 0, ',', '.');
                })
                ->addColumn('keuntungan_formatted', function ($row) {
                    $class = $row->total_keuntungan >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' . $class . '">Rp ' . number_format($row->total_keuntungan, 0, ',', '.') . '</span>';
                })
                ->addColumn('details_url', function ($row) use ($jenisPembayaran) {
                    // Return the API endpoint without HTML encoding
                    $url = route('laporan.penjualan.index') . '?get_details=1&penjualan_id=' . $row->id;
                    // Add payment type filter if set
                    if (!empty($jenisPembayaran)) {
                        $url .= '&jenis_pembayaran=' . $jenisPembayaran;
                    }
                    return $url;
                })
                ->rawColumns(['jenis_formatted', 'keuntungan_formatted'])
                ->make(true);
        }

        // Get summary data for the selected period
        $summaryQuery = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->select(
                DB::raw('SUM(penjualan_details.total) as total_penjualan'),
                DB::raw('SUM(penjualan_details.harga_beli * penjualan_details.jumlah) as total_hpp'),
                DB::raw('SUM((penjualan_details.harga * penjualan_details.jumlah) - penjualan_details.diskon + penjualan_details.ppn - (penjualan_details.harga_beli * penjualan_details.jumlah)) as total_keuntungan')
            )
            ->whereBetween('penjualans.tanggal_penjualan', [$startDate, $endDate . ' 23:59:59']);

        // Apply payment type filter if selected
        if (!empty($jenisPembayaran)) {
            $summaryQuery->where('penjualans.jenis', $jenisPembayaran);
        }

        $summary = $summaryQuery->first();

        return view('laporan.penjualan.index', compact('startDate', 'endDate', 'jenisPembayaran', 'summary'));
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
        $jenisPembayaran = $request->input('jenis_pembayaran');

        // Get detailed sales data with profit calculation
        $query = PenjualanDetail::join('penjualans', 'penjualans.id', '=', 'penjualan_details.penjualan_id')
            ->join('obat', 'obat.id', '=', 'penjualan_details.obat_id')
            ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'penjualan_details.satuan_id')
            ->select(
                'penjualans.no_faktur',
                'penjualans.tanggal_penjualan',
                'penjualans.jenis',
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

        // Apply payment type filter if selected
        if (!empty($jenisPembayaran)) {
            $query->where('penjualans.jenis', $jenisPembayaran);
        }

        $salesData = $query->orderBy('penjualans.tanggal_penjualan', 'desc')->get();

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
            'jenisPembayaran',
            'salesData',
            'summary'
        ));

        return $pdf->download('laporan-penjualan-' . $startDate . '-' . $endDate . '.pdf');
    }
}
