<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Obat;
use App\Models\SatuanObat;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class LaporanPembelianController extends Controller
{
    /**
     * Display the purchase report page with date filter.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Default: show current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPembayaran = $request->input('jenis_pembayaran');
        $supplierId = $request->input('supplier_id');
        $obatId = $request->input('obat_id');

        if ($request->ajax()) {
            if ($request->filled('get_details')) {
                // This is a request for child row details
                $pembelianId = $request->input('pembelian_id');

                $details = PembelianDetail::where('pembelian_id', $pembelianId)
                    ->join('obat', 'obat.id', '=', 'pembelian_detail.obat_id')
                    ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'pembelian_detail.satuan_id')
                    ->select(
                        'pembelian_detail.id',
                        'obat.nama_obat',
                        'satuan_obat.nama as satuan',
                        'pembelian_detail.harga_beli',
                        'pembelian_detail.jumlah',
                        'pembelian_detail.subtotal',
                        'pembelian_detail.diskon_persen',
                        'pembelian_detail.diskon_nominal',
                        'pembelian_detail.hpp_per_unit',
                        'pembelian_detail.margin_jual_persen',
                        'pembelian_detail.harga_jual_per_unit',
                        'pembelian_detail.no_batch',
                        'pembelian_detail.tanggal_expired',
                        'pembelian_detail.total'
                    )
                    ->get();

                return response()->json($details);
            }

            if ($request->filled('summary_only')) {
                // Just return the summary data
                $summaryQuery = PembelianDetail::join('pembelian', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
                    ->select(
                        DB::raw('SUM(pembelian_detail.total) as total_pembelian'),
                        DB::raw('SUM(pembelian_detail.diskon_nominal) as total_diskon'),
                        DB::raw('COUNT(DISTINCT pembelian.id) as total_faktur')
                    )
                    ->whereBetween('pembelian.tanggal_faktur', [$startDate, $endDate]);

                // Apply payment type filter if selected
                if (!empty($jenisPembayaran)) {
                    $summaryQuery->where('pembelian.jenis', $jenisPembayaran);
                }

                $summary = $summaryQuery->first();

                return response()->json(['summary' => $summary]);
            }

            // Get parent rows (grouped by pembelian)
            $query = Pembelian::leftJoin('users', 'users.id', '=', 'pembelian.user_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'pembelian.supplier_id')
                ->select(
                    'pembelian.id',
                    'pembelian.no_po',
                    'pembelian.no_faktur',
                    'pembelian.tanggal_faktur',
                    'pembelian.jenis',
                    'pembelian.status_pembayaran',
                    'suppliers.nama as nama_supplier',
                    'pembelian.subtotal',
                    'pembelian.diskon_total',
                    'pembelian.ppn_total',
                    'pembelian.grand_total',
                    'users.name as user_name',
                    'pembelian.tanggal_jatuh_tempo'
                )
                ->whereBetween('pembelian.tanggal_faktur', [$startDate, $endDate]);

            // Apply payment type filter if selected
            if (!empty($jenisPembayaran)) {
                $query->where('pembelian.jenis', $jenisPembayaran);
            }

            // Apply supplier filter if selected
            if (!empty($supplierId)) {
                $query->where('pembelian.supplier_id', $supplierId);
            }

            // Apply obat (medicine) filter if selected
            if (!empty($obatId)) {
                $query->whereExists(function ($subquery) use ($obatId) {
                    $subquery->select(DB::raw(1))
                        ->from('pembelian_detail')
                        ->whereRaw('pembelian_detail.pembelian_id = pembelian.id')
                        ->where('pembelian_detail.obat_id', $obatId);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('supplier', function ($row) {
                    return $row->nama_supplier ?? '-';
                })
                ->addColumn('tanggal_formatted', function ($row) {
                    return Carbon::parse($row->tanggal_faktur)->format('d/m/Y');
                })
                ->addColumn('jenis_formatted', function ($row) {
                    $badgeClass = '';
                    switch ($row->jenis) {
                        case 'TUNAI':
                            $badgeClass = 'badge-light-success';
                            break;
                        case 'HUTANG':
                            $badgeClass = 'badge-light-warning';
                            break;
                        case 'KONSINYASI':
                            $badgeClass = 'badge-light-primary';
                            break;
                        default:
                            $badgeClass = 'badge-light-info';
                    }
                    return '<span class="badge ' . $badgeClass . '">' . $row->jenis . '</span>';
                })
                ->addColumn('status_pembayaran_formatted', function ($row) {
                    if ($row->jenis !== 'HUTANG') {
                        return $row->jenis === 'TUNAI' ?
                            '<span class="badge badge-light-success">LUNAS</span>' :
                            '<span class="badge badge-light-primary">KONSINYASI</span>';
                    }

                    $badgeClass = '';
                    switch ($row->status_pembayaran) {
                        case 'BELUM':
                            $badgeClass = 'badge-light-danger';
                            break;
                        case 'SEBAGIAN':
                            $badgeClass = 'badge-light-warning';
                            break;
                        case 'LUNAS':
                            $badgeClass = 'badge-light-success';
                            break;
                        default:
                            $badgeClass = 'badge-light-danger';
                    }
                    return '<span class="badge ' . $badgeClass . '">' . $row->status_pembayaran . '</span>';
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
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . number_format($row->grand_total, 0, ',', '.');
                })
                ->addColumn('jatuh_tempo_formatted', function ($row) {
                    if ($row->jenis !== 'HUTANG' || !$row->tanggal_jatuh_tempo) {
                        return '-';
                    }

                    $today = Carbon::now()->startOfDay();
                    $jatuhTempo = Carbon::parse($row->tanggal_jatuh_tempo)->startOfDay();

                    $label = Carbon::parse($row->tanggal_jatuh_tempo)->format('d/m/Y');
                    $badgeClass = 'badge-light-primary';

                    if ($today->gt($jatuhTempo)) {
                        $badgeClass = 'badge-light-danger';
                        $label .= ' (TERLAMBAT)';
                    } elseif ($today->eq($jatuhTempo)) {
                        $badgeClass = 'badge-light-warning';
                        $label .= ' (HARI INI)';
                    } elseif ($today->diffInDays($jatuhTempo) <= 7) {
                        $badgeClass = 'badge-light-warning';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                })
                ->addColumn('details_url', function ($row) use ($jenisPembayaran, $supplierId, $obatId) {
                    // Return the API endpoint without HTML encoding
                    $url = route('laporan.pembelian.index') . '?get_details=1&pembelian_id=' . $row->id;
                    // Add payment type filter if set
                    if (!empty($jenisPembayaran)) {
                        $url .= '&jenis_pembayaran=' . $jenisPembayaran;
                    }
                    // Add supplier filter if set
                    if (!empty($supplierId)) {
                        $url .= '&supplier_id=' . $supplierId;
                    }
                    // Add obat filter if set
                    if (!empty($obatId)) {
                        $url .= '&obat_id=' . $obatId;
                    }
                    return $url;
                })
                ->rawColumns(['jenis_formatted', 'status_pembayaran_formatted', 'jatuh_tempo_formatted'])
                ->make(true);
        }

        // Get summary data for the selected period
        $summaryQuery = PembelianDetail::join('pembelian', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->select(
                DB::raw('SUM(pembelian_detail.total) as total_pembelian'),
                DB::raw('SUM(pembelian_detail.diskon_nominal) as total_diskon'),
                DB::raw('COUNT(DISTINCT pembelian.id) as total_faktur')
            )
            ->whereBetween('pembelian.tanggal_faktur', [$startDate, $endDate]);

        // Apply payment type filter if selected
        if (!empty($jenisPembayaran)) {
            $summaryQuery->where('pembelian.jenis', $jenisPembayaran);
        }

        // Apply supplier filter if selected
        if (!empty($supplierId)) {
            $summaryQuery->where('pembelian.supplier_id', $supplierId);
        }

        // Apply obat (medicine) filter if selected
        if (!empty($obatId)) {
            $summaryQuery->where('pembelian_detail.obat_id', $obatId);
        }

        $summary = $summaryQuery->first();

        // Get suppliers for dropdown
        $suppliers = \App\Models\Supplier::orderBy('nama')->get();

        // Get medicines for dropdown
        $obats = \App\Models\Obat::orderBy('nama_obat')->get();

        return view('laporan.pembelian.index', compact(
            'startDate',
            'endDate',
            'jenisPembayaran',
            'supplierId',
            'obatId',
            'suppliers',
            'obats',
            'summary'
        ));
    }

    /**
     * Export purchase report to PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $jenisPembayaran = $request->input('jenis_pembayaran');
        $supplierId = $request->input('supplier_id');
        $obatId = $request->input('obat_id');

        // Get detailed purchase data
        $query = PembelianDetail::join('pembelian', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
            ->join('obat', 'obat.id', '=', 'pembelian_detail.obat_id')
            ->leftJoin('satuan_obat', 'satuan_obat.id', '=', 'pembelian_detail.satuan_id')
            ->leftJoin('supplier', 'supplier.id', '=', 'pembelian.supplier_id')
            ->select(
                'pembelian.no_faktur',
                'pembelian.no_po',
                'pembelian.tanggal_faktur',
                'supplier.nama as nama_supplier',
                'pembelian.jenis',
                'pembelian.status_pembayaran',
                'obat.nama_obat',
                'satuan_obat.nama as satuan',
                'pembelian_detail.harga_beli',
                'pembelian_detail.jumlah',
                'pembelian_detail.subtotal',
                'pembelian_detail.diskon_persen',
                'pembelian_detail.diskon_nominal',
                'pembelian_detail.hpp_per_unit',
                'pembelian_detail.margin_jual_persen',
                'pembelian_detail.harga_jual_per_unit',
                'pembelian_detail.total',
                'pembelian_detail.no_batch',
                'pembelian_detail.tanggal_expired'
            )
            ->whereBetween('pembelian.tanggal_faktur', [$startDate, $endDate]);

        // Apply payment type filter if selected
        if (!empty($jenisPembayaran)) {
            $query->where('pembelian.jenis', $jenisPembayaran);
        }

        // Apply supplier filter if selected
        if (!empty($supplierId)) {
            $query->where('pembelian.supplier_id', $supplierId);
        }

        // Apply obat (medicine) filter if selected
        if (!empty($obatId)) {
            $query->where('pembelian_detail.obat_id', $obatId);
        }

        $purchaseData = $query->orderBy('pembelian.tanggal_faktur', 'desc')->get();

        // Calculate summary
        $totalPembelian = $purchaseData->sum('total');
        $totalDiskon = $purchaseData->sum('diskon_nominal');
        $totalFaktur = $purchaseData->groupBy('no_faktur')->count();

        $summary = [
            'total_pembelian' => $totalPembelian,
            'total_diskon' => $totalDiskon,
            'total_faktur' => $totalFaktur,
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('laporan.pembelian.pdf', compact(
            'startDate',
            'endDate',
            'jenisPembayaran',
            'supplierId',
            'obatId',
            'purchaseData',
            'summary'
        ));

        return $pdf->download('laporan-pembelian-' . $startDate . '-' . $endDate . '.pdf');
    }
}
