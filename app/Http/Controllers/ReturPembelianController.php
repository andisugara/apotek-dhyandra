<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Stok;
use App\Models\TransaksiAkun;
use App\Models\Akun;
use App\Models\LokasiObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ReturPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ReturPembelian::with(['pembelian.supplier', 'user'])
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('supplier_nama', function ($row) {
                    return $row->pembelian->supplier ? $row->pembelian->supplier->nama : '-';
                })
                ->addColumn('no_faktur', function ($row) {
                    return $row->pembelian ? $row->pembelian->no_faktur : '-';
                })
                ->addColumn('tanggal_retur_formatted', function ($row) {
                    return $row->tanggal_retur->format('d/m/Y');
                })
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . $row->formatted_grand_total;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('retur_pembelian.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('retur_pembelian.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();
        return view('retur_pembelian.create', compact('lokasis'));
    }

    /**
     * Search for pembelian by nomor faktur for the retur process.
     */
    public function searchPembelian(Request $request)
    {
        $noFaktur = $request->input('no_faktur');
        
        // Search for pembelian with the given nomor faktur
        $pembelian = Pembelian::with([
                'supplier', 
                'details.obat', 
                'details.satuan',
                'details.stok'
            ])
            ->where('no_faktur', 'like', '%' . $noFaktur . '%')
            ->first();
            
        if (!$pembelian) {
            return response()->json([
                'success' => false,
                'message' => 'Pembelian dengan nomor faktur tersebut tidak ditemukan.'
            ]);
        }

        // Check if there are returnable items
        $hasReturnableItems = false;
        foreach ($pembelian->details as $detail) {
            // Calculate already returned quantity
            $returnedQty = $detail->returDetails()->sum('jumlah');
            
            // Calculate remaining quantity that can be returned
            $remainingQty = $detail->jumlah - $returnedQty;
            
            if ($remainingQty > 0) {
                $hasReturnableItems = true;
                
                // Add remaining qty to the detail object
                $detail->remaining_qty = $remainingQty;
            } else {
                $detail->remaining_qty = 0;
            }
        }

        if (!$hasReturnableItems) {
            return response()->json([
                'success' => false,
                'message' => 'Semua item pada pembelian ini sudah diretur sepenuhnya.'
            ]);
        }

        return response()->json([
            'success' => true,
            'pembelian' => $pembelian
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate main retur data
        $rules = [
            'pembelian_id' => 'required|exists:pembelian,id',
            'tanggal_retur' => 'required|date',
            'alasan' => 'required|string',
            'detail' => 'required|array|min:1',
            'detail.*.pembelian_detail_id' => 'required|exists:pembelian_detail,id',
            'detail.*.obat_id' => 'required|exists:obat,id',
            'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.no_batch' => 'required|string',
            'detail.*.lokasi_id' => 'required|exists:lokasi_obat,id',
        ];

        // Validate the request
        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Generate unique retur number (REF-YYYYMMDD-XXXX)
            $today = now()->format('Ymd');
            $lastRetur = ReturPembelian::where('no_retur', 'like', "REF-$today%")
                ->orderBy('no_retur', 'desc')
                ->first();

            $sequence = '0001';
            if ($lastRetur) {
                $lastSequence = intval(substr($lastRetur->no_retur, -4));
                $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            }

            $noRetur = "REF-$today-$sequence";

            // Create retur pembelian
            $returPembelian = ReturPembelian::create([
                'no_retur' => $noRetur,
                'tanggal_retur' => $validated['tanggal_retur'],
                'pembelian_id' => $validated['pembelian_id'],
                'alasan' => $validated['alasan'],
                'subtotal' => 0, // Will be calculated from details
                'ppn_total' => $request->ppn_total ?? 0,
                'grand_total' => 0, // Will be calculated from details
                'user_id' => Auth::id(),
            ]);

            $subtotal = 0;
            $grandTotal = 0;

            // Get the pembelian for calculating return values
            $pembelian = Pembelian::findOrFail($validated['pembelian_id']);
            
            // Process detail items
            foreach ($validated['detail'] as $detail) {
                // Skip items with 0 quantity
                if (intval($detail['jumlah']) <= 0) {
                    continue;
                }
                
                // Get the original pembelian detail
                $pembelianDetail = PembelianDetail::findOrFail($detail['pembelian_detail_id']);
                
                // Calculate values
                $hargaBeli = $pembelianDetail->harga_beli;
                $jumlah = intval($detail['jumlah']);
                $subtotalItem = $hargaBeli * $jumlah;
                
                // Calculate PPN for this item based on the overall PPN percentage from the original purchase
                $ppnPercentage = $pembelian->subtotal > 0 ? 
                    ($pembelian->ppn_total / $pembelian->subtotal) * 100 : 0;
                
                $ppnItem = ($ppnPercentage / 100) * $subtotalItem;
                $totalItem = $subtotalItem + $ppnItem;
                
                // Create detail record
                $returDetail = ReturPembelianDetail::create([
                    'retur_pembelian_id' => $returPembelian->id,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'obat_id' => $detail['obat_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalItem,
                    'ppn' => $ppnItem,
                    'total' => $totalItem,
                    'no_batch' => $detail['no_batch'],
                    'tanggal_expired' => $pembelianDetail->tanggal_expired,
                    'lokasi_id' => $detail['lokasi_id']
                ]);
                
                // Update stock - reduce the quantity from the specific batch
                $stok = Stok::where('pembelian_detail_id', $pembelianDetail->id)
                    ->where('no_batch', $detail['no_batch'])
                    ->first();
                
                if ($stok) {
                    $stok->qty -= $jumlah;
                    $stok->save();
                }
                
                // Add to totals
                $subtotal += $subtotalItem;
                $grandTotal += $totalItem;
            }

            // Update retur with calculated totals
            $returPembelian->update([
                'subtotal' => $subtotal,
                'ppn_total' => $grandTotal - $subtotal,
                'grand_total' => $grandTotal
            ]);

            // Create accounting transaction if the original purchase was cash
            if ($pembelian->jenis === 'TUNAI' && $pembelian->akun_kas_id) {
                TransaksiAkun::create([
                    'akun_id' => $pembelian->akun_kas_id,
                    'tanggal' => $returPembelian->tanggal_retur,
                    'kode_referensi' => 'RET-' . $returPembelian->id,
                    'tipe_referensi' => 'RETUR_PEMBELIAN',
                    'referensi_id' => $returPembelian->id,
                    'deskripsi' => 'Retur pembelian untuk faktur ' . $pembelian->no_faktur,
                    'debit' => 0,
                    'kredit' => $grandTotal, // For returns, credit the account
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('retur_pembelian.index')->with('success', 'Retur pembelian berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $returPembelian = ReturPembelian::with([
                'pembelian.supplier',
                'user',
                'details.obat',
                'details.satuan',
                'details.lokasi',
                'details.pembelianDetail',
                'transaksiAkun'
            ])
            ->findOrFail($id);

        return view('retur_pembelian.show', compact('returPembelian'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $returPembelian = ReturPembelian::findOrFail($id);

        DB::beginTransaction();
        try {
            // Restore stock quantities
            foreach ($returPembelian->details as $detail) {
                $stok = Stok::where('pembelian_detail_id', $detail->pembelian_detail_id)
                    ->where('no_batch', $detail->no_batch)
                    ->first();
                
                if ($stok) {
                    $stok->qty += $detail->jumlah;
                    $stok->save();
                }
            }

            // Delete transaction records
            TransaksiAkun::where('referensi_id', $returPembelian->id)
                ->where('tipe_referensi', 'RETUR_PEMBELIAN')
                ->delete();

            // Delete retur (details will be deleted via cascade)
            $returPembelian->delete();

            DB::commit();
            return redirect()->route('retur_pembelian.index')->with('success', 'Retur pembelian berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
