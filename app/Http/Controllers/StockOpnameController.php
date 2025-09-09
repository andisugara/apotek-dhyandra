<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Stok;
use App\Models\LokasiObat;
use App\Models\SatuanObat;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockOpname::with('user')
                ->orderBy('tanggal', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_formatted', function ($row) {
                    return $row->tanggal->format('d/m/Y');
                })
                ->addColumn('petugas', function ($row) {
                    return $row->user->name;
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status == 'draft') {
                        return '<span class="badge badge-light-warning">Draft</span>';
                    } else {
                        return '<span class="badge badge-light-success">Selesai</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('stock_opname.show', $row) . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Lihat Detail">
                                    <i class="ki-duotone ki-eye fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </a>';

                    if ($row->status == 'draft') {
                        $actionBtn .= '<a href="' . route('stock_opname.edit', $row) . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Edit">
                                        <i class="ki-duotone ki-pencil fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </a>';

                        $actionBtn .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete" data-id="' . $row->id . '" title="Hapus">
                                        <i class="ki-duotone ki-trash fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    </button>';
                    }

                    $actionBtn .= '<a href="' . route('stock_opname.print', $row) . '" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm" title="Print" target="_blank">
                                    <i class="ki-duotone ki-printer fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                </a>';

                    return $actionBtn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('stock_opname.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $locations = LokasiObat::all();

        // Generate unique stock opname code
        $todayCode = 'SO-' . date('Ymd');
        $lastStockOpname = StockOpname::where('kode', 'like', $todayCode . '%')
            ->orderBy('kode', 'desc')
            ->first();

        if ($lastStockOpname) {
            $lastNumber = (int) substr($lastStockOpname->kode, -3);
            $newNumber = $lastNumber + 1;
            $kode = $todayCode . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $kode = $todayCode . '-001';
        }

        return view('stock_opname.create', compact('locations', 'kode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|unique:stock_opnames',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $stockOpname = StockOpname::create([
                'kode' => $request->kode,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'status' => 'draft',
                'user_id' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('stock_opname.edit', $stockOpname)
                ->with('success', 'Stock opname berhasil dibuat, silahkan lanjutkan dengan menambahkan obat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['details', 'details.obat', 'details.satuan', 'details.lokasi', 'user']);

        return view('stock_opname.show', compact('stockOpname'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'selesai') {
            return redirect()->route('stock_opname.show', $stockOpname)
                ->with('error', 'Stock opname yang sudah selesai tidak dapat diedit.');
        }

        $stockOpname->load(['details', 'details.obat', 'details.satuan', 'details.lokasi']);
        $locations = LokasiObat::all();

        return view('stock_opname.edit', compact('stockOpname', 'locations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'selesai') {
            return redirect()->route('stock_opname.index')
                ->with('error', 'Stock opname yang sudah selesai tidak dapat diubah.');
        }

        $request->validate([
            'keterangan' => 'nullable|string',
            'status' => 'required|in:draft,selesai',
        ]);

        DB::beginTransaction();

        try {
            $stockOpname->update([
                'keterangan' => $request->keterangan,
                'status' => $request->status,
            ]);

            DB::commit();

            return redirect()->route('stock_opname.index')
                ->with('success', 'Stock opname berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'selesai') {
            return redirect()->route('stock_opname.index')
                ->with('error', 'Stock opname yang sudah selesai tidak dapat dihapus.');
        }

        DB::beginTransaction();

        try {
            // Delete all details first, then the stock opname
            $stockOpname->details()->delete();
            $stockOpname->delete();

            DB::commit();

            return redirect()->route('stock_opname.index')
                ->with('success', 'Stock opname berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('stock_opname.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Search for medicine to add to stock opname
     */
    public function searchObat(Request $request)
    {
        $lokasi_id = $request->lokasi_id;
        $keyword = $request->q;

        try {
            $obats = Obat::with(['stok' => function ($query) use ($lokasi_id) {
                $query->where('lokasi_id', $lokasi_id);
            }])
                ->where(function ($query) use ($keyword) {
                    $query->where('nama_obat', 'like', "%{$keyword}%")
                        ->orWhere('kode_obat', 'like', "%{$keyword}%");
                })
                ->limit(10)
                ->get();

            // Log successful search
            Log::info('Search obat success', [
                'lokasi_id' => $lokasi_id,
                'keyword' => $keyword,
                'results_count' => $obats->count()
            ]);

            return response()->json($obats);
        } catch (\Exception $e) {
            // Log error
            Log::error('Search obat error', [
                'lokasi_id' => $lokasi_id,
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get stock details for a medicine
     */
    public function getStokDetail(Request $request)
    {
        $obat_id = $request->obat_id;
        $lokasi_id = $request->lokasi_id;

        try {
            // Validate input
            if (!$obat_id || !$lokasi_id) {
                Log::warning('Invalid parameters for getStokDetail', [
                    'obat_id' => $obat_id,
                    'lokasi_id' => $lokasi_id
                ]);
                return response()->json(['error' => 'Obat ID dan Lokasi ID diperlukan'], 400);
            }

            $stoks = Stok::with(['satuan', 'lokasi'])
                ->where('obat_id', $obat_id)
                ->where('lokasi_id', $lokasi_id)
                ->where('jumlah', '>', 0)
                ->get();

            // Format dates to be Y-m-d for JavaScript date inputs
            $stoks->each(function ($stok) {
                if ($stok->tanggal_expired) {
                    $stok->tanggal_expired = Carbon::parse($stok->tanggal_expired)->format('Y-m-d');
                }
            });

            Log::info('Get stok detail success', [
                'obat_id' => $obat_id,
                'lokasi_id' => $lokasi_id,
                'results_count' => $stoks->count()
            ]);

            return response()->json($stoks);
        } catch (\Exception $e) {
            Log::error('Get stok detail error', [
                'obat_id' => $obat_id,
                'lokasi_id' => $lokasi_id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add medicine to stock opname
     */
    public function addObat(Request $request, StockOpname $stockOpname)
    {
        Log::info('Adding obat to stock opname', [
            'request' => $request->all(),
            'stockOpname' => $stockOpname->id
        ]);

        try {
            $request->validate([
                'obat_id' => 'required|exists:obats,id',
                'satuan_id' => 'required|exists:satuan_obats,id',
                'lokasi_id' => 'required|exists:lokasi_obats,id',
                'no_batch' => 'required|string',
                'tanggal_expired' => 'required|date',
                'stok_sistem' => 'required|integer|min:0',
                'stok_fisik' => 'required|integer|min:0',
                'tindakan' => 'nullable|string',
                'catatan' => 'nullable|string',
            ]);

            if ($stockOpname->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock opname yang sudah selesai tidak dapat diubah.'
                ], 422);
            }

            // Check if this item already exists in the stock opname
            $existing = StockOpnameDetail::where('stock_opname_id', $stockOpname->id)
                ->where('obat_id', $request->obat_id)
                ->where('satuan_id', $request->satuan_id)
                ->where('lokasi_id', $request->lokasi_id)
                ->where('no_batch', $request->no_batch)
                ->first();

            if ($existing) {
                Log::warning('Item already exists in stock opname', ['detail' => $existing]);
                return response()->json([
                    'success' => false,
                    'message' => 'Obat dengan batch yang sama sudah ada di stock opname ini.'
                ], 422);
            }

            DB::beginTransaction();

            try {
                $selisih = (int)$request->stok_fisik - (int)$request->stok_sistem;

                // Parse and format tanggal_expired to ensure consistent format
                $tanggalExpired = Carbon::parse($request->tanggal_expired)->format('Y-m-d');

                $detail = StockOpnameDetail::create([
                    'stock_opname_id' => $stockOpname->id,
                    'obat_id' => $request->obat_id,
                    'satuan_id' => $request->satuan_id,
                    'lokasi_id' => $request->lokasi_id,
                    'no_batch' => $request->no_batch,
                    'tanggal_expired' => $tanggalExpired,
                    'stok_sistem' => (int)$request->stok_sistem,
                    'stok_fisik' => (int)$request->stok_fisik,
                    'selisih' => $selisih,
                    'tindakan' => $request->tindakan,
                    'catatan' => $request->catatan,
                ]);

                Log::info('Successfully added obat to stock opname', ['detail' => $detail->id]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Obat berhasil ditambahkan ke stock opname.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error in DB transaction when adding obat to stock opname', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error when adding obat to stock opname', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error adding obat to stock opname', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove medicine from stock opname
     */
    public function removeObat(Request $request, StockOpname $stockOpname, StockOpnameDetail $detail)
    {
        if ($stockOpname->status === 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Stock opname yang sudah selesai tidak dapat diubah.'
            ], 422);
        }

        if ($detail->stock_opname_id !== $stockOpname->id) {
            return response()->json([
                'success' => false,
                'message' => 'Detail tidak ditemukan dalam stock opname ini.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $detail->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Obat berhasil dihapus dari stock opname.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete the stock opname process
     */
    public function complete(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status === 'selesai') {
            return redirect()->route('stock_opname.show', $stockOpname)
                ->with('error', 'Stock opname ini sudah berstatus selesai.');
        }

        if ($stockOpname->details->isEmpty()) {
            return redirect()->route('stock_opname.edit', $stockOpname)
                ->with('error', 'Stock opname harus memiliki minimal satu item obat.');
        }

        DB::beginTransaction();

        try {
            // Update stock opname status
            $stockOpname->update([
                'status' => 'selesai'
            ]);

            // Update stock quantities based on the physical count
            foreach ($stockOpname->details as $detail) {
                // If there's a difference between system and physical count
                if ($detail->selisih !== 0) {
                    $stok = Stok::where('obat_id', $detail->obat_id)
                        ->where('satuan_id', $detail->satuan_id)
                        ->where('lokasi_id', $detail->lokasi_id)
                        ->where('no_batch', $detail->no_batch)
                        ->first();

                    if ($stok) {
                        $stok->jumlah = $detail->stok_fisik;
                        $stok->save();
                    }
                }
            }

            DB::commit();

            return redirect()->route('stock_opname.show', $stockOpname)
                ->with('success', 'Stock opname berhasil diselesaikan dan stok telah diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Print stock opname report
     */
    public function print(StockOpname $stockOpname)
    {
        $stockOpname->load(['details', 'details.obat', 'details.satuan', 'details.lokasi', 'user']);

        return view('stock_opname.print', compact('stockOpname'));
    }
}
