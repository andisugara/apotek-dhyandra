<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Stok;
use App\Models\TransaksiAkun;
use App\Models\LokasiObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ReturPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ReturPenjualan::with(['penjualan.pasien', 'user'])
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('pasien_nama', function ($row) {
                    return $row->penjualan->pasien ? $row->penjualan->pasien->nama : '-';
                })
                ->addColumn('no_faktur', function ($row) {
                    return $row->penjualan ? $row->penjualan->no_faktur : '-';
                })
                ->addColumn('tanggal_retur_formatted', function ($row) {
                    return $row->tanggal_retur->format('d/m/Y');
                })
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . $row->formatted_grand_total;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('retur_penjualan.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('retur_penjualan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();
        return view('retur_penjualan.create', compact('lokasis'));
    }

    /**
     * Search for penjualan by nomor faktur for Select2.
     */
    public function searchSelect2(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $page = (int)$request->input('page', 1);
            $perPage = 10;

            $query = Penjualan::with(['pasien', 'details.returDetails'])
                ->where(function ($q) use ($search) {
                    if ($search) {
                        $q->where('no_faktur', 'like', '%' . $search . '%');
                    }
                })
                ->orderBy('tanggal_penjualan', 'desc');

            $total = $query->count();

            $results = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = [];
            foreach ($results as $penjualan) {
                // Check if the penjualan has details
                if (!$penjualan->details || $penjualan->details->isEmpty()) {
                    continue;
                }

                // Check if any items are returnable
                $hasReturnableItems = false;
                foreach ($penjualan->details as $detail) {
                    // Calculate returned quantity - handle potential relationship issues
                    $returnedQty = 0;
                    if ($detail->returDetails) {
                        $returnedQty = $detail->returDetails->sum('jumlah');
                    }

                    $remainingQty = $detail->jumlah - $returnedQty;
                    if ($remainingQty > 0) {
                        $hasReturnableItems = true;
                        break;
                    }
                }

                if ($hasReturnableItems) {
                    $data[] = [
                        'id' => $penjualan->id,
                        'text' => $penjualan->no_faktur,
                        'no_faktur' => $penjualan->no_faktur,
                        'tanggal_penjualan' => $penjualan->tanggal_penjualan,
                        'pasien' => $penjualan->pasien
                    ];
                }
            }

            return response()->json([
                'data' => $data,
                'pagination' => [
                    'more' => $total > ($page * $perPage)
                ],
                'total_count' => $total,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'data' => [],
                'pagination' => ['more' => false]
            ]);
        }
    }

    /**
     * Search for penjualan by ID for the retur process.
     */
    public function searchById(Request $request)
    {
        try {
            $penjualanId = $request->input('penjualan_id');

            // Search for penjualan with the given ID
            $penjualan = Penjualan::with([
                'pasien',
                'details.obat',
                'details.satuan',
                'details.returDetails'
            ])
                ->find($penjualanId);

            if (!$penjualan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penjualan tidak ditemukan.'
                ]);
            }

            // Make sure details is initialized
            if (!$penjualan->details) {
                $penjualan->details = [];
            }

            // Check if there are returnable items and calculate quantities
            $hasReturnableItems = false;
            foreach ($penjualan->details as $detail) {
                // Calculate already returned quantity - handle potential relationship issues
                $returnedQty = 0;
                if (method_exists($detail, 'returDetails') && $detail->returDetails) {
                    $returnedQty = $detail->returDetails->sum('jumlah');
                }

                // Calculate remaining quantity that can be returned
                $remainingQty = $detail->jumlah - $returnedQty;

                // Store both values on the detail object
                $detail->remaining_qty = $remainingQty;
                $detail->returned_qty = $returnedQty;

                if ($remainingQty > 0) {
                    $hasReturnableItems = true;
                }

                // Check for relationship issues that might cause JavaScript errors
                if (!$detail->obat) {
                    // Log this issue for debugging
                    Log::warning('Missing obat relationship for penjualan_detail_id: ' . $detail->id);
                }

                if (!$detail->satuan) {
                    Log::warning('Missing satuan relationship for penjualan_detail_id: ' . $detail->id);
                }
            }

            if (!$hasReturnableItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua item pada penjualan ini sudah diretur sepenuhnya.'
                ]);
            }

            return response()->json([
                'success' => true,
                'penjualan' => $penjualan
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchById: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate main retur data
        $rules = [
            'penjualan_id' => 'required|exists:penjualans,id',
            'tanggal_retur' => 'required|date',
            'alasan' => 'required|string',
            'detail' => 'required|array|min:1',
            'detail.*.penjualan_detail_id' => 'required|exists:penjualan_details,id',
            'detail.*.obat_id' => 'required|exists:obat,id',
            'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
            'detail.*.jumlah' => 'required|integer|min:0', // Changed to min:0 to allow 0 quantities
            'detail.*.lokasi_id' => 'required|exists:lokasi_obat,id',
        ];

        // Validate the request
        $validated = $request->validate($rules);

        // Filter out items with 0 quantity
        $detailsWithQuantity = array_filter($validated['detail'], function ($item) {
            return intval($item['jumlah']) > 0;
        });

        // Check if there's at least one item with quantity > 0
        if (empty($detailsWithQuantity)) {
            return redirect()->back()->with('error', 'Minimal satu item harus memiliki jumlah retur lebih dari 0')->withInput();
        }

        // Replace the original details with filtered ones
        $validated['detail'] = $detailsWithQuantity;

        DB::beginTransaction();
        try {
            // Generate unique retur number (RET-YYYYMMDD-XXXX)
            $today = now()->format('Ymd');
            $lastRetur = ReturPenjualan::where('no_retur', 'like', "RET-$today%")
                ->orderBy('no_retur', 'desc')
                ->first();

            $sequence = '0001';
            if ($lastRetur) {
                $lastSequence = intval(substr($lastRetur->no_retur, -4));
                $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            }

            $noRetur = "RET-$today-$sequence";

            // Create retur penjualan
            $returPenjualan = ReturPenjualan::create([
                'no_retur' => $noRetur,
                'tanggal_retur' => $validated['tanggal_retur'],
                'penjualan_id' => $validated['penjualan_id'],
                'alasan' => $validated['alasan'],
                'subtotal' => 0, // Will be calculated from details
                'diskon_total' => 0, // Will be calculated from details
                'ppn_total' => 0, // Will be calculated from details
                'grand_total' => 0, // Will be calculated from details
                'user_id' => Auth::id(),
            ]);

            $subtotal = 0;
            $diskonTotal = 0;
            $ppnTotal = 0;
            $grandTotal = 0;

            // Get the penjualan for calculating return values
            $penjualan = Penjualan::findOrFail($validated['penjualan_id']);

            // Process detail items
            foreach ($validated['detail'] as $detail) {
                // All items should have quantity > 0 due to our earlier filter
                // But let's keep this check for extra safety
                if (intval($detail['jumlah']) <= 0) {
                    continue;
                }

                // Get the original penjualan detail
                $penjualanDetail = PenjualanDetail::findOrFail($detail['penjualan_detail_id']);

                // Validate return quantity doesn't exceed available quantity
                $returnedQty = $penjualanDetail->returDetails()->sum('jumlah');
                $maxReturnQty = $penjualanDetail->jumlah - $returnedQty;
                $requestedReturnQty = intval($detail['jumlah']);

                if ($requestedReturnQty > $maxReturnQty) {
                    throw new \Exception("Jumlah retur untuk item {$penjualanDetail->obat->nama_obat} melebihi stok yang tersedia");
                }

                // Calculate values
                $hargaBeli = $penjualanDetail->harga_beli;
                $hargaJual = $penjualanDetail->harga; // This is the selling price
                $jumlah = $requestedReturnQty;
                $subtotalItem = $hargaJual * $jumlah;

                // Calculate diskon and ppn proportionally
                $diskonPercentage = $penjualanDetail->subtotal > 0 ?
                    ($penjualanDetail->diskon / $penjualanDetail->subtotal) * 100 : 0;

                $ppnPercentage = $penjualan->subtotal > 0 ?
                    ($penjualan->ppn_total / $penjualan->subtotal) * 100 : 0;

                $diskonItem = ($diskonPercentage / 100) * $subtotalItem;
                $ppnItem = ($ppnPercentage / 100) * $subtotalItem;
                $totalItem = $subtotalItem - $diskonItem + $ppnItem;

                // Create detail record
                $returDetail = ReturPenjualanDetail::create([
                    'retur_penjualan_id' => $returPenjualan->id,
                    'penjualan_detail_id' => $penjualanDetail->id,
                    'obat_id' => $detail['obat_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'subtotal' => $subtotalItem,
                    'diskon' => $diskonItem,
                    'ppn' => $ppnItem,
                    'total' => $totalItem,
                    'no_batch' => $penjualanDetail->no_batch,
                    'tanggal_expired' => $penjualanDetail->tanggal_expired,
                    'lokasi_id' => $detail['lokasi_id']
                ]);

                // Update stock - add the quantity back to stock
                // First, try to find existing stock record with the same batch
                $stok = Stok::where('obat_id', $detail['obat_id'])
                    ->where('satuan_id', $detail['satuan_id'])
                    ->where('lokasi_id', $detail['lokasi_id'])
                    ->where('no_batch', $penjualanDetail->no_batch)
                    ->first();

                if ($stok) {
                    // Update existing stock
                    $stok->qty += $jumlah;
                    $stok->save();
                } else {
                    // Create new stock record
                    Stok::create([
                        'obat_id' => $detail['obat_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'lokasi_id' => $detail['lokasi_id'],
                        'no_batch' => $penjualanDetail->no_batch,
                        'tanggal_expired' => $penjualanDetail->tanggal_expired,
                        'qty' => $jumlah,
                        'qty_awal' => $jumlah
                    ]);
                }

                // Add to totals
                $subtotal += $subtotalItem;
                $diskonTotal += $diskonItem;
                $ppnTotal += $ppnItem;
                $grandTotal += $totalItem;
            }

            // Update retur with calculated totals
            $returPenjualan->update([
                'subtotal' => $subtotal,
                'diskon_total' => $diskonTotal,
                'ppn_total' => $ppnTotal,
                'grand_total' => $grandTotal
            ]);

            // Create accounting transaction if the original purchase was cash
            if ($penjualan->jenis === 'TUNAI') {
                // Find the cash account ID from the last transaction
                $lastTransaction = TransaksiAkun::where('referensi_id', $penjualan->id)
                    ->where('tipe_referensi', 'PENJUALAN')
                    ->first();

                if ($lastTransaction) {
                    TransaksiAkun::create([
                        'akun_id' => $lastTransaction->akun_id,
                        'tanggal' => $returPenjualan->tanggal_retur,
                        'kode_referensi' => 'RET-' . $returPenjualan->id,
                        'tipe_referensi' => 'RETUR_PENJUALAN',
                        'referensi_id' => $returPenjualan->id,
                        'deskripsi' => 'Retur penjualan untuk faktur ' . $penjualan->no_faktur,
                        'debit' => $grandTotal, // For returns, debit the account (reduce cash)
                        'kredit' => 0,
                        'user_id' => Auth::id()
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('retur_penjualan.index')->with('success', 'Retur penjualan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating retur penjualan: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $returPenjualan = ReturPenjualan::with([
            'penjualan.pasien',
            'user',
            'details.obat',
            'details.satuan',
            'details.lokasi',
            'details.penjualanDetail',
            'transaksiAkun'
        ])
            ->findOrFail($id);

        return view('retur_penjualan.show', compact('returPenjualan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $returPenjualan = ReturPenjualan::findOrFail($id);

        DB::beginTransaction();
        try {
            // Reduce stock quantities
            foreach ($returPenjualan->details as $detail) {
                $stok = Stok::where('obat_id', $detail->obat_id)
                    ->where('satuan_id', $detail->satuan_id)
                    ->where('lokasi_id', $detail->lokasi_id)
                    ->where('no_batch', $detail->no_batch)
                    ->first();

                if ($stok) {
                    // Make sure stock doesn't go negative
                    if ($stok->qty < $detail->jumlah) {
                        throw new \Exception("Stok tidak mencukupi untuk menghapus retur");
                    }

                    $stok->qty -= $detail->jumlah;
                    $stok->save();
                } else {
                    throw new \Exception("Stok tidak ditemukan");
                }
            }

            // Delete transaction records
            TransaksiAkun::where('referensi_id', $returPenjualan->id)
                ->where('tipe_referensi', 'RETUR_PENJUALAN')
                ->delete();

            // Delete retur (details will be deleted via cascade)
            $returPenjualan->delete();

            DB::commit();
            return redirect()->route('retur_penjualan.index')->with('success', 'Retur penjualan berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
