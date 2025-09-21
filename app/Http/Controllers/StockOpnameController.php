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
        $locations = LokasiObat::where('is_active', true)->get();

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
            Log::error('Error creating stock opname: ' . $e->getMessage());

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
        // Just get the active locations for default selection
        $locations = LokasiObat::where('is_active', true)->get();

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
            Log::error('Error updating stock opname: ' . $e->getMessage());

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

            return response()->json([
                'success' => true,
                'message' => 'Stock opname berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting stock opname: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for medicine to add to stock opname
     */
    public function searchObat(Request $request)
    {
        $keyword = $request->q;

        // Log the search request with all request details for debugging
        Log::info('Search obat request', [
            'keyword' => $keyword,
            'request_all' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method()
        ]);

        // Return empty if no keyword or keyword too short
        if (empty($keyword) || strlen($keyword) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false]
            ]);
        }

        try {
            // Search for medicines matching the keyword in name or code
            $obats = Obat::query()
                ->where(function ($q) use ($keyword) {
                    $q->where('nama_obat', 'like', "%{$keyword}%")
                        ->orWhere('kode_obat', 'like', "%{$keyword}%");
                })
                ->select('id', 'nama_obat', 'kode_obat')
                ->limit(10)
                ->get();

            // Format results in Select2 compatible format
            $results = [];

            foreach ($obats as $obat) {
                $results[] = [
                    'id' => $obat->id,
                    'text' => $obat->nama_obat . ' (' . $obat->kode_obat . ')',
                ];
            }

            // Log successful search
            Log::info('Search obat success', [
                'keyword' => $keyword,
                'results_count' => count($results),
                'results' => $results
            ]);

            return response()->json([
                'results' => $results,
                'pagination' => ['more' => false]
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Search obat error', [
                'keyword' => $keyword,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'results' => []
            ], 500);
        }
    }

    /**
     * Get stock details for a medicine including all available units
     */
    public function getStokDetail(Request $request)
    {
        // Log the incoming request with headers for debugging
        Log::info('Get stock detail request', [
            'obat_id' => $request->obat_id,
            'method' => $request->method(),
            'has_token' => $request->hasHeader('X-CSRF-TOKEN'),
            'token' => $request->header('X-CSRF-TOKEN') ? 'present' : 'missing',
            'message' => 'Getting stock without location dependency'
        ]);

        $obat_id = $request->obat_id;
        $lokasi_id = $request->lokasi_id ?? LokasiObat::where('is_active', true)->first()->id;

        try {
            // Validate input
            if (!$obat_id) {
                Log::warning('Invalid parameters for getStokDetail', [
                    'obat_id' => $obat_id
                ]);
                return response()->json(['error' => 'ID Obat diperlukan'], 400);
            }

            // Get obat details with its units
            $obat = Obat::with(['satuans.satuan'])->find($obat_id);

            if (!$obat) {
                return response()->json(['error' => 'Obat tidak ditemukan'], 404);
            }

            $result = [];

            // Get all units for this obat
            foreach ($obat->satuans as $obatSatuan) {
                if (!$obatSatuan->satuan) continue;

                // Calculate total stock for this medicine and unit regardless of location
                $totalStok = Stok::where('obat_satuan_id', $obatSatuan->id)
                    ->sum('qty');

                // Ensure stock is not null
                $totalStok = $totalStok ?: 0;

                $result[] = [
                    'obat_id' => $obat_id,
                    'obat_nama' => $obat->nama_obat,
                    'obat_kode' => $obat->kode_obat,
                    'satuan_id' => $obatSatuan->satuan_id,
                    'satuan_nama' => $obatSatuan->satuan->nama,
                    'stok_sistem' => $totalStok,
                ];
            }

            Log::info('Get stok detail success', [
                'obat_id' => $obat_id,
                'results_count' => count($result)
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Get stok detail error', [
                'obat_id' => $obat_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            // Basic validation for direct addition
            $request->validate([
                'obat_id' => 'required|exists:obat,id',
                'stok_sistem' => 'nullable|numeric|min:0',
                'stok_fisik' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string',
            ]);

            // Check if satuan_id is missing, meaning it's a direct addition
            if (!$request->has('satuan_id')) {
                // Get the default unit for this medicine
                $obat = Obat::with('satuans.satuan')->findOrFail($request->obat_id);

                if ($obat->satuans->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Obat ini tidak memiliki satuan. Silahkan tambahkan satuan terlebih dahulu.'
                    ], 422);
                }

                // Use the first unit as default
                $firstSatuan = $obat->satuans->first();
                $request->merge(['satuan_id' => $firstSatuan->satuan_id]);

                // Ensure stok_sistem and stok_fisik have default values
                if (!$request->has('stok_sistem')) {
                    $request->merge(['stok_sistem' => 0]);
                }

                if (!$request->has('stok_fisik')) {
                    $request->merge(['stok_fisik' => 0]);
                }
            }

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
                ->first();

            if ($existing) {
                Log::warning('Item already exists in stock opname', ['detail' => $existing]);
                return response()->json([
                    'success' => false,
                    'message' => 'Obat dengan satuan yang sama sudah ada di stock opname ini.'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Convert to float with proper decimal handling
                $stokSistem = floatval($request->stok_sistem);
                $stokFisik = floatval($request->stok_fisik);
                $selisih = $stokFisik - $stokSistem;

                // Round to 2 decimal places to avoid floating point precision issues
                $selisih = round($selisih, 2);

                $detail = StockOpnameDetail::create([
                    'stock_opname_id' => $stockOpname->id,
                    'obat_id' => $request->obat_id,
                    'satuan_id' => $request->satuan_id,
                    'lokasi_id' => $request->lokasi_id,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'keterangan' => $request->keterangan,
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
            Log::error('Error removing item from stock opname: ' . $e->getMessage());

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
            // Update stock opname status to completed
            $stockOpname->update([
                'status' => 'selesai'
            ]);

            // No stock adjustments are made since this is just a checking procedure
            // This is just for recording the stock state at a point in time

            DB::commit();

            return redirect()->route('stock_opname.show', $stockOpname)
                ->with('success', 'Stock opname berhasil diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing stock opname: ' . $e->getMessage());

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
