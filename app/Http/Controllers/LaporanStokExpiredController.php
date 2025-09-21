<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Stok;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LaporanStokExpiredController extends Controller
{
    /**
     * Display the expired stock report page.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Base query for expired or soon-to-expire stock
            $query = Stok::with([
                'obat',
                'obatSatuan.satuan',
                'obat.golongan',
                'obat.kategori'
            ])
                ->where('qty', '>', 0) // Only include items with stock
                ->where(function ($q) use ($request) {
                    // Filter by expiration status
                    $status = $request->status;
                    $today = Carbon::today();
                    $thirtyDaysFromNow = Carbon::today()->addDays(30);

                    if ($status === 'expired') {
                        // Expired items (date is in the past)
                        $q->where('tanggal_expired', '<', $today);
                    } else if ($status === 'soon') {
                        // Soon to expire (within 30 days)
                        $q->whereBetween('tanggal_expired', [$today, $thirtyDaysFromNow]);
                    } else {
                        // Default: show both expired and soon-to-expire
                        $q->where('tanggal_expired', '<', $thirtyDaysFromNow);
                    }
                })
                ->orderBy('tanggal_expired', 'asc'); // Order by closest expiration first

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nama_obat', function ($row) {
                    return $row->obat ? $row->obat->nama_obat : '-';
                })
                ->addColumn('kode_obat', function ($row) {
                    return $row->obat ? $row->obat->kode_obat : '-';
                })
                ->addColumn('satuan', function ($row) {
                    return $row->obatSatuan && $row->obatSatuan->satuan
                        ? $row->obatSatuan->satuan->nama
                        : ($row->satuan ? $row->satuan->nama : '-');
                })
                ->addColumn('golongan', function ($row) {
                    return $row->obat && $row->obat->golongan
                        ? $row->obat->golongan->nama
                        : '-';
                })
                ->addColumn('kategori', function ($row) {
                    return $row->obat && $row->obat->kategori
                        ? $row->obat->kategori->nama
                        : '-';
                })
                ->addColumn('tanggal_expired_formatted', function ($row) {
                    return $row->tanggal_expired ? $row->tanggal_expired->format('d/m/Y') : '-';
                })
                ->addColumn('status_expired', function ($row) {
                    $today = Carbon::today();

                    if ($row->tanggal_expired < $today) {
                        return '<span class="badge badge-light-danger">Expired</span>';
                    } else {
                        $daysLeft = $today->diffInDays($row->tanggal_expired, false);
                        if ($daysLeft <= 30) {
                            return '<span class="badge badge-light-warning">Segera Expired (' . $daysLeft . ' hari)</span>';
                        } else {
                            return '<span class="badge badge-light-success">Baik</span>';
                        }
                    }
                })
                ->addColumn('lokasi', function ($row) {
                    return $row->lokasi ? $row->lokasi->nama : '-';
                })
                ->rawColumns(['status_expired'])
                ->make(true);
        }

        return view('laporan.stok_expired.index');
    }

    /**
     * Export the expired stock report to PDF.
     */
    public function exportPdf(Request $request)
    {
        // Get data based on filters
        $status = $request->status;
        $today = Carbon::today();
        $thirtyDaysFromNow = Carbon::today()->addDays(30);

        $query = Stok::with([
            'obat',
            'obatSatuan.satuan',
            'obat.golongan',
            'obat.kategori',
            'lokasi'
        ])
            ->where('qty', '>', 0);

        // Apply filters
        if ($status === 'expired') {
            $query->where('tanggal_expired', '<', $today);
            $title = 'Laporan Stok Obat Expired';
        } else if ($status === 'soon') {
            $query->whereBetween('tanggal_expired', [$today, $thirtyDaysFromNow]);
            $title = 'Laporan Stok Obat Segera Expired (30 Hari)';
        } else {
            $query->where('tanggal_expired', '<', $thirtyDaysFromNow);
            $title = 'Laporan Stok Obat Expired & Segera Expired';
        }

        $data = $query->orderBy('tanggal_expired', 'asc')->get();

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setPaper('a4', 'landscape');
        $pdf->loadView('laporan.stok_expired.pdf', compact('data', 'title', 'today'));

        return $pdf->stream('laporan-stok-expired-' . now()->format('Ymd-His') . '.pdf');
    }
}
