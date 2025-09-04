<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use App\Models\Pabrik;
use App\Models\GolonganObat;
use App\Models\KategoriObat;
use App\Models\SatuanObat;
use App\Models\LokasiObat;
use App\Models\ObatSatuan;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ObatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Obat::with(['pabrik', 'golongan', 'kategori'])->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_pabrik', function ($row) {
                    return $row->pabrik ? $row->pabrik->nama : '-';
                })
                ->addColumn('nama_golongan', function ($row) {
                    return $row->golongan ? $row->golongan->nama : '-';
                })
                ->addColumn('nama_kategori', function ($row) {
                    return $row->kategori ? $row->kategori->nama : '-';
                })
                ->addColumn('status_label', function ($row) {
                    $statusClass = $row->is_active == '1' ? 'badge-light-success' : 'badge-light-danger';
                    return '<span class="badge ' . $statusClass . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('obat.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a> ';
                    $actionBtn .= '<a href="' . route('obat.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }

        return view('obat.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pabriks = Pabrik::where('status', '1')->orderBy('nama')->get();
        $golongans = GolonganObat::where('is_active', '1')->orderBy('nama')->get();
        $kategoris = KategoriObat::where('is_active', '1')->orderBy('nama')->get();
        $satuans = SatuanObat::where('is_active', '1')->orderBy('nama')->get();
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();

        return view('obat.create', compact('pabriks', 'golongans', 'kategoris', 'satuans', 'lokasis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate main obat data
        $validated = $request->validate([
            'kode_obat' => 'nullable|string|max:20|unique:obat,kode_obat',
            'nama_obat' => 'required|string|max:255|min:3',
            'pabrik_id' => 'required|exists:pabrik,id',
            'golongan_id' => 'required|exists:golongan_obat,id',
            'kategori_id' => 'required|exists:kategori_obat,id',
            'jenis_obat' => 'required|string',
            'minimal_stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'indikasi' => 'nullable|string',
            'kandungan' => 'nullable|string',
            'dosis' => 'nullable|string',
            'kemasan' => 'nullable|string',
            'efek_samping' => 'nullable|string',
            'zat_aktif_prekursor' => 'nullable|string',
            'aturan_pakai' => 'nullable|string',
            'is_active' => 'required|in:0,1',
        ]);

        // Handle kode obat
        if (empty($validated['kode_obat'])) {
            // Generate kode obat jika tidak diisi
            $date = date('ymd');
            $last = Obat::whereDate('created_at', now())->count() + 1;
            $kode = 'OBT' . $date . str_pad($last, 4, '0', STR_PAD_LEFT);

            // Check if generated kode already exists, if so, increment until finding unique one
            $counter = $last;
            while (Obat::where('kode_obat', $kode)->exists()) {
                $counter++;
                $kode = 'OBT' . $date . str_pad($counter, 4, '0', STR_PAD_LEFT);
            }
            $validated['kode_obat'] = $kode;
        } else {
            // Ensure kode always starts with OBT
            if (!str_starts_with($validated['kode_obat'], 'OBT')) {
                $validated['kode_obat'] = 'OBT' . $validated['kode_obat'];
            }
        }

        DB::beginTransaction();
        try {
            // Create obat
            $obat = Obat::create($validated);

            // Handle satuan data if provided
            if ($request->has('satuan') && is_array($request->satuan)) {
                foreach ($request->satuan as $satuan) {
                    if (!empty($satuan['satuan_id'])) {
                        ObatSatuan::create([
                            'obat_id' => $obat->id,
                            'satuan_id' => $satuan['satuan_id'],
                            'harga_beli' => $satuan['harga_beli'] ?? 0,
                            'diskon_persen' => $satuan['diskon_persen'] ?? 0,
                            'profit_persen' => $satuan['profit_persen'] ?? 10,
                            'harga_jual' => $satuan['harga_jual'] ?? 0,
                        ]);
                    }
                }
            }

            // Handle stok data if provided
            if ($request->has('stok') && is_array($request->stok)) {
                foreach ($request->stok as $stok) {
                    if (!empty($stok['satuan_id']) && !empty($stok['lokasi_id'])) {
                        // Check if the satuan exists for this obat
                        $satuanExists = ObatSatuan::where('obat_id', $obat->id)
                            ->where('satuan_id', $stok['satuan_id'])
                            ->exists();

                        if ($satuanExists) {
                            // Get the initial quantity value
                            $qty = $stok['qty'] ?? 0;

                            Stok::create([
                                'obat_id' => $obat->id,
                                'satuan_id' => $stok['satuan_id'],
                                'lokasi_id' => $stok['lokasi_id'],
                                'no_batch' => $stok['no_batch'],
                                'tanggal_expired' => $stok['tanggal_expired'],
                                'qty' => $qty,
                                'qty_awal' => $qty, // Set qty_awal to the same as qty initially
                                'pembelian_detail_id' => $stok['pembelian_detail_id'] ?? null,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('obat.index')->with('success', 'Data obat berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $obat = Obat::with(['pabrik', 'golongan', 'kategori', 'satuans.satuan', 'stok.lokasi', 'stok.satuan'])->findOrFail($id);
        $satuans = SatuanObat::where('is_active', '1')->orderBy('nama')->get();
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();

        return view('obat.show', compact('obat', 'satuans', 'lokasis'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $obat = Obat::with(['pabrik', 'golongan', 'kategori', 'satuans.satuan', 'stok.lokasi', 'stok.satuan'])->findOrFail($id);
        $pabriks = Pabrik::where('status', '1')->orderBy('nama')->get();
        $golongans = GolonganObat::where('is_active', '1')->orderBy('nama')->get();
        $kategoris = KategoriObat::where('is_active', '1')->orderBy('nama')->get();
        $satuans = SatuanObat::where('is_active', '1')->orderBy('nama')->get();
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();

        return view('obat.edit', compact('obat', 'pabriks', 'golongans', 'kategoris', 'satuans', 'lokasis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $obat = Obat::findOrFail($id);

        // Validate main obat data
        $validated = $request->validate([
            'kode_obat' => 'required|string|max:20|unique:obat,kode_obat,' . $id,
            'nama_obat' => 'required|string|max:255|min:3',
            'pabrik_id' => 'required|exists:pabrik,id',
            'golongan_id' => 'required|exists:golongan_obat,id',
            'kategori_id' => 'required|exists:kategori_obat,id',
            'jenis_obat' => 'required|string',
            'minimal_stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'indikasi' => 'nullable|string',
            'kandungan' => 'nullable|string',
            'dosis' => 'nullable|string',
            'kemasan' => 'nullable|string',
            'efek_samping' => 'nullable|string',
            'zat_aktif_prekursor' => 'nullable|string',
            'aturan_pakai' => 'nullable|string',
            'is_active' => 'required|in:0,1',
        ]);

        // Ensure kode always starts with OBT
        if (!str_starts_with($validated['kode_obat'], 'OBT')) {
            $validated['kode_obat'] = 'OBT' . $validated['kode_obat'];
        }

        DB::beginTransaction();
        try {
            // Update obat
            $obat->update($validated);

            // Handle satuan data if provided
            if ($request->has('satuan') && is_array($request->satuan)) {
                // Get existing satuan IDs
                $existingSatuanIds = $obat->satuans->pluck('id')->toArray();
                $updatedSatuanIds = [];

                foreach ($request->satuan as $satuan) {
                    if (!empty($satuan['satuan_id'])) {
                        // Check if satuan already exists for this obat
                        $obatSatuan = ObatSatuan::where('obat_id', $obat->id)
                            ->where('satuan_id', $satuan['satuan_id'])
                            ->first();

                        if ($obatSatuan) {
                            // Update existing satuan
                            $obatSatuan->update([
                                'harga_beli' => $satuan['harga_beli'] ?? $obatSatuan->harga_beli,
                                'diskon_persen' => $satuan['diskon_persen'] ?? $obatSatuan->diskon_persen,
                                'profit_persen' => $satuan['profit_persen'] ?? $obatSatuan->profit_persen ?? 10,
                                'harga_jual' => $satuan['harga_jual'] ?? $obatSatuan->harga_jual,
                            ]);
                            $updatedSatuanIds[] = $obatSatuan->id;
                        } else {
                            // Create new satuan
                            $newObatSatuan = ObatSatuan::create([
                                'obat_id' => $obat->id,
                                'satuan_id' => $satuan['satuan_id'],
                                'harga_beli' => $satuan['harga_beli'] ?? 0,
                                'diskon_persen' => $satuan['diskon_persen'] ?? 0,
                                'profit_persen' => $satuan['profit_persen'] ?? 10,
                                'harga_jual' => $satuan['harga_jual'] ?? 0,
                            ]);
                            $updatedSatuanIds[] = $newObatSatuan->id;
                        }
                    }
                }

                // Delete satuans that are not in the updated list
                $satuansToDelete = array_diff($existingSatuanIds, $updatedSatuanIds);
                if (!empty($satuansToDelete)) {
                    // Check if any stock depends on these satuans
                    $hasStock = Stok::where('obat_id', $obat->id)
                        ->whereIn('satuan_id', function ($query) use ($satuansToDelete) {
                            $query->select('satuan_id')
                                ->from('obat_satuan')
                                ->whereIn('id', $satuansToDelete);
                        })->exists();

                    if ($hasStock) {
                        throw new \Exception('Tidak dapat menghapus satuan karena masih memiliki stok');
                    }

                    ObatSatuan::whereIn('id', $satuansToDelete)->delete();
                }
            }

            // Handle stok data if provided
            if ($request->has('stok') && is_array($request->stok)) {
                foreach ($request->stok as $stok) {
                    if (!empty($stok['satuan_id']) && !empty($stok['lokasi_id'])) {
                        // Check if the satuan exists for this obat
                        $satuanExists = ObatSatuan::where('obat_id', $obat->id)
                            ->where('satuan_id', $stok['satuan_id'])
                            ->exists();

                        if ($satuanExists) {
                            // Check if this is an existing stock or new one
                            if (isset($stok['id']) && $stok['id']) {
                                $existingStock = Stok::find($stok['id']);
                                if ($existingStock) {
                                    $existingStock->update([
                                        'lokasi_id' => $stok['lokasi_id'],
                                        'no_batch' => $stok['no_batch'],
                                        'tanggal_expired' => $stok['tanggal_expired'],
                                        'qty' => $stok['qty'] ?? $existingStock->qty,
                                    ]);
                                }
                            } else {
                                // Get the initial quantity value
                                $qty = $stok['qty'] ?? 0;

                                Stok::create([
                                    'obat_id' => $obat->id,
                                    'satuan_id' => $stok['satuan_id'],
                                    'lokasi_id' => $stok['lokasi_id'],
                                    'no_batch' => $stok['no_batch'],
                                    'tanggal_expired' => $stok['tanggal_expired'],
                                    'qty' => $qty,
                                    'qty_awal' => $qty, // Set qty_awal to the same as qty initially
                                    'pembelian_detail_id' => $stok['pembelian_detail_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            // Handle satuans to delete
            if ($request->has('delete_satuan') && is_array($request->delete_satuan)) {
                foreach ($request->delete_satuan as $satuanId) {
                    $satuanToDelete = ObatSatuan::where('id', $satuanId)
                        ->where('obat_id', $obat->id)
                        ->first();

                    if ($satuanToDelete) {
                        // Check if any stock depends on this satuan
                        $hasStock = Stok::where('obat_id', $obat->id)
                            ->where('satuan_id', $satuanToDelete->satuan_id)
                            ->exists();

                        if ($hasStock) {
                            throw new \Exception('Tidak dapat menghapus satuan karena masih memiliki stok');
                        }

                        // Delete the satuan
                        $satuanToDelete->delete();
                    }
                }
            }

            // Handle stocks to delete
            if ($request->has('delete_stok') && is_array($request->delete_stok)) {
                foreach ($request->delete_stok as $stockId) {
                    $stockToDelete = Stok::where('id', $stockId)
                        ->where('obat_id', $obat->id)
                        ->first();

                    if ($stockToDelete) {
                        // Check if this stock is linked to a purchase detail
                        if ($stockToDelete->pembelian_detail_id) {
                            throw new \Exception('Tidak dapat menghapus stok yang terkait dengan pembelian');
                        }

                        // Delete the stock
                        $stockToDelete->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('obat.index')->with('success', 'Data obat berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $obat = Obat::findOrFail($id);

        DB::beginTransaction();
        try {
            // Check if obat has any stock
            if ($obat->stok->count() > 0) {
                throw new \Exception('Obat tidak dapat dihapus karena masih memiliki stok');
            }

            // Delete all satuans
            ObatSatuan::where('obat_id', $id)->delete();

            // Delete obat
            $obat->delete();

            DB::commit();
            return redirect()->route('obat.index')->with('success', 'Data obat berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get satuans for a specific obat
     */
    public function getSatuans($id)
    {
        $satuans = ObatSatuan::with('satuan')
            ->where('obat_id', $id)
            ->get();
        return response()->json($satuans);
    }

    /**
     * Add a new satuan to an obat
     */
    public function addSatuan(Request $request, $id)
    {
        $validated = $request->validate([
            'satuan_id' => 'required|exists:satuan_obats,id',
            'harga_beli' => 'required|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        try {
            $obat = Obat::findOrFail($id);

            // Check if satuan already exists
            $existingSatuan = ObatSatuan::where('obat_id', $id)
                ->where('satuan_id', $validated['satuan_id'])
                ->first();

            if ($existingSatuan) {
                return response()->json(['error' => 'Satuan ini sudah ditambahkan untuk obat ini'], 400);
            }

            $obatSatuan = ObatSatuan::create([
                'obat_id' => $id,
                'satuan_id' => $validated['satuan_id'],
                'harga_beli' => $validated['harga_beli'],
                'diskon_persen' => $validated['diskon_persen'] ?? 0,
                'harga_jual' => $validated['harga_jual'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil ditambahkan',
                'data' => $obatSatuan->load('satuan')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove a satuan from an obat
     */
    public function removeSatuan($id, $satuanId)
    {
        try {
            $obatSatuan = ObatSatuan::where('obat_id', $id)
                ->where('satuan_id', $satuanId)
                ->first();

            if (!$obatSatuan) {
                return response()->json(['error' => 'Satuan tidak ditemukan'], 404);
            }

            // Check if any stock depends on this satuan
            $hasStock = Stok::where('obat_id', $id)
                ->where('satuan_id', $satuanId)
                ->exists();

            if ($hasStock) {
                return response()->json([
                    'error' => 'Tidak dapat menghapus satuan karena masih memiliki stok'
                ], 400);
            }

            $obatSatuan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add a new stock to an obat
     */
    public function addStock(Request $request, $id)
    {
        $validated = $request->validate([
            'satuan_id' => 'required|exists:satuan_obats,id',
            'lokasi_id' => 'required|exists:lokasi_obats,id',
            'no_batch' => 'required|string',
            'tanggal_expired' => 'required|date|after:today',
            'qty' => 'required|integer|min:1',
        ]);

        try {
            $obat = Obat::findOrFail($id);

            // Check if the satuan exists for this obat
            $satuanExists = ObatSatuan::where('obat_id', $id)
                ->where('satuan_id', $validated['satuan_id'])
                ->exists();

            if (!$satuanExists) {
                return response()->json([
                    'error' => 'Satuan ini belum ditambahkan untuk obat ini, tambahkan satuan terlebih dahulu'
                ], 400);
            }

            $qty = $validated['qty'];

            $stok = Stok::create([
                'obat_id' => $id,
                'satuan_id' => $validated['satuan_id'],
                'lokasi_id' => $validated['lokasi_id'],
                'no_batch' => $validated['no_batch'],
                'tanggal_expired' => $validated['tanggal_expired'],
                'qty' => $qty,
                'qty_awal' => $qty, // Set qty_awal to the same as qty initially
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil ditambahkan',
                'data' => $stok->load(['satuan', 'lokasi'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, $id)
    {
        $validated = $request->validate([
            'id' => 'required|exists:stok,id',
            'qty' => 'required|integer|min:0',
        ]);

        try {
            $stok = Stok::findOrFail($validated['id']);

            if ($stok->obat_id != $id) {
                return response()->json(['error' => 'Data tidak valid'], 400);
            }

            $stok->update(['qty' => $validated['qty']]);

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diupdate',
                'data' => $stok->load(['satuan', 'lokasi'])
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove a stock
     */
    public function removeStock($id, $stockId)
    {
        try {
            $stok = Stok::where('id', $stockId)
                ->where('obat_id', $id)
                ->first();

            if (!$stok) {
                return response()->json(['error' => 'Stok tidak ditemukan'], 404);
            }

            // Check if this stock is linked to a purchase detail
            if ($stok->pembelian_detail_id) {
                return response()->json([
                    'error' => 'Tidak dapat menghapus stok yang terkait dengan pembelian'
                ], 400);
            }

            $stok->delete();

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}
