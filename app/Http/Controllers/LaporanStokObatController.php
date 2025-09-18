<?php

namespace App\Http\Controllers;

use App\Models\ObatSatuan;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanStokObatController extends Controller
{
    /**
     * Tampilkan laporan stok obat per satuan.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ObatSatuan::with(['obat.golongan', 'obat.kategori', 'satuan', 'stok']);

            return datatables()->of($query)
                ->addIndexColumn()
                ->editColumn('kode_obat', function ($row) {
                    return $row->obat->kode_obat ?? '-';
                })
                ->editColumn('nama_obat', function ($row) {
                    return $row->obat->nama_obat ?? '-';
                })
                ->editColumn('golongan', function ($row) {
                    return $row->obat->golongan->nama ?? '-';
                })
                ->editColumn('kategori', function ($row) {
                    return $row->obat->kategori->nama ?? '-';
                })
                ->editColumn('satuan', function ($row) {
                    return $row->satuan->nama ?? '-';
                })
                ->editColumn('stok', function ($row) {
                    // Jumlahkan qty dari semua stok yang terkait dengan ObatSatuan ini
                    return $row->stok;
                })
                ->editColumn('status', function ($row) {
                    if (isset($row->obat->is_active)) {
                        $label = $row->obat->is_active == 1 ? 'Aktif' : 'Non Aktif';
                        $class = $row->obat->is_active == 1 ? 'bg-success' : 'bg-danger';
                        return '<span class="badge ' . $class . '">' . $label . '</span>';
                    }
                    return '-';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        // Untuk tampilan awal (bukan ajax)
        $data = [];
        return view('laporan.stok_obat', compact('data'));
    }
}
