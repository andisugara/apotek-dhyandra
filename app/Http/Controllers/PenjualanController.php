<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Obat;
use App\Models\ObatSatuan;
use App\Models\Pasien;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Stok;
use App\Models\TransaksiAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class PenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Penjualan::with(['pasien', 'user'])->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_formatted', function ($row) {
                    return $row->tanggal_penjualan->format('d/m/Y');
                })
                ->addColumn('pasien_nama', function ($row) {
                    return $row->pasien ? $row->pasien->nama : 'Umum';
                })
                ->addColumn('jenis_display', function ($row) {
                    return $row->jenis_display;
                })
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . number_format($row->grand_total, 0, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penjualan.show', $row->id) . '"
                                    class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary">
                                    <i class="ki-outline ki-eye fs-2"></i>
                                </a>
                                <a href="' . route('penjualan.print', $row->id) . '"
                                    class="btn btn-sm btn-icon btn-bg-light btn-active-color-success" target="_blank">
                                    <i class="ki-outline ki-printer fs-2"></i>
                                </a>
                                <button type="button" data-id="' . $row->id . '"
                                    class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger btn-delete">
                                    <i class="ki-outline ki-trash fs-2"></i>
                                </button>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('penjualan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pasiens = Pasien::all();
        $obats = Obat::with(['stok' => function ($query) {
            $query->where('qty', '>', 0)->orderBy('tanggal_expired', 'asc');
        }, 'satuans'])->get();
        return view('penjualan.create', compact('pasiens', 'obats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'tanggal_penjualan' => 'required|date',
            'pasien_id' => 'nullable|exists:pasien,id',
            'jenis' => 'required|in:TUNAI,NON_TUNAI',
            'keterangan' => 'nullable|string',
            'subtotal' => 'required|numeric|min:0',
            'diskon_total' => 'required|numeric|min:0',
            'ppn_total' => 'required|numeric|min:0',
            'tuslah_total' => 'required|numeric|min:0',
            'embalase_total' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
            'kembalian' => 'required|numeric|min:0',
            'detail' => 'required|array|min:1',
            'detail.*.obat_id' => 'required|exists:obat,id',
            'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.harga' => 'required|numeric|min:0',
            'detail.*.subtotal' => 'required|numeric|min:0',
            'detail.*.diskon' => 'required|numeric|min:0',
            'detail.*.ppn' => 'required|numeric|min:0',
            'detail.*.tuslah' => 'required|numeric|min:0',
            'detail.*.embalase' => 'required|numeric|min:0',
            'detail.*.total' => 'required|numeric|min:0',
            'detail.*.no_batch' => 'required|string',
            'detail.*.lokasi_id' => 'required|exists:lokasi_obat,id',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique invoice number (INV-YYYYMMDD-XXXX)
            $today = now()->format('Ymd');
            $lastPenjualan = Penjualan::where('no_faktur', 'like', "INV-$today%")
                ->orderBy('no_faktur', 'desc')
                ->first();

            $sequence = '0001';
            if ($lastPenjualan) {
                $lastSequence = intval(substr($lastPenjualan->no_faktur, -4));
                $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
            }

            $noFaktur = "INV-$today-$sequence";

            // Create penjualan
            $penjualan = Penjualan::create([
                'no_faktur' => $noFaktur,
                'tanggal_penjualan' => $validated['tanggal_penjualan'],
                'pasien_id' => $validated['pasien_id'],
                'jenis' => $validated['jenis'],
                'subtotal' => $validated['subtotal'],
                'diskon_total' => $validated['diskon_total'],
                'ppn_total' => $validated['ppn_total'],
                'tuslah_total' => $validated['tuslah_total'],
                'embalase_total' => $validated['embalase_total'],
                'grand_total' => $validated['grand_total'],
                'bayar' => $validated['bayar'],
                'kembalian' => $validated['kembalian'],
                'keterangan' => $validated['keterangan'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // Create penjualan details
            foreach ($validated['detail'] as $detail) {
                // Get the obat info
                $obat = Obat::findOrFail($detail['obat_id']);

                // Create detail record
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $detail['obat_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'jumlah' => $detail['jumlah'],
                    'harga_beli' => 0, // Default value, will be updated below from stock
                    'harga' => $detail['harga'],
                    'subtotal' => $detail['subtotal'],
                    'diskon' => $detail['diskon'],
                    'ppn' => $detail['ppn'],
                    'tuslah' => $detail['tuslah'],
                    'embalase' => $detail['embalase'],
                    'total' => $detail['total'],
                    'no_batch' => $detail['no_batch'],
                    'tanggal_expired' => null, // Will be updated below
                    'lokasi_id' => $detail['lokasi_id']
                ]);

                // Get the ObatSatuan record for this obat and satuan
                $obatSatuan = ObatSatuan::where('obat_id', $detail['obat_id'])
                    ->where('satuan_id', $detail['satuan_id'])
                    ->first();

                // Update stock - mencari berdasarkan no_batch dan obat_satuan_id
                $stok = Stok::where('no_batch', $detail['no_batch'])
                    ->where('obat_satuan_id', $obatSatuan ? $obatSatuan->id : null)
                    ->where('lokasi_id', $detail['lokasi_id'])
                    ->first();

                if ($stok) {
                    // Update expiry date and harga_beli in detail
                    $penjualanDetail->update([
                        'tanggal_expired' => $stok->tanggal_expired,
                        'harga_beli' => $stok->harga_beli
                    ]);

                    // Update stock quantity
                    $stok->qty -= $detail['jumlah'];
                    $stok->save();

                    // Log for debugging
                    Log::info("Updated stock for obat {$obat->nama_obat}, batch {$detail['no_batch']}, new qty: {$stok->qty}");
                } else {
                    Log::warning("Stock not found for obat {$obat->nama_obat}, batch {$detail['no_batch']}");
                }
            }

            // Create accounting transaction for cash sales
            if ($penjualan->jenis === 'TUNAI') {
                // Get kas akun (adjust as needed based on your system)
                $kasAkunId = Akun::where('is_default', true)->first()->id; // Default, adjust this based on your system setup
                $pendapatanAkunId = Akun::where('is_default', true)->first()->id; // Akun pendapatan penjualan, sesuaikan dengan sistem Anda

                // 1. Jurnal kas - Uang masuk ke kas (debit)
                TransaksiAkun::create([
                    'akun_id' => $kasAkunId,
                    'tanggal' => $penjualan->tanggal_penjualan,
                    'kode_referensi' => 'PNJ-' . $penjualan->id,
                    'tipe_referensi' => 'PENJUALAN',
                    'referensi_id' => $penjualan->id,
                    'deskripsi' => 'Penerimaan kas dari penjualan dengan no faktur ' . $penjualan->no_faktur,
                    'debit' => $penjualan->grand_total,
                    'kredit' => 0,
                    'user_id' => Auth::id()
                ]);

                // 2. Jurnal pendapatan - Pendapatan penjualan (kredit)
                TransaksiAkun::create([
                    'akun_id' => $pendapatanAkunId,
                    'tanggal' => $penjualan->tanggal_penjualan,
                    'kode_referensi' => 'PNJ-' . $penjualan->id,
                    'tipe_referensi' => 'PENJUALAN',
                    'referensi_id' => $penjualan->id,
                    'deskripsi' => 'Pendapatan penjualan obat dengan no faktur ' . $penjualan->no_faktur,
                    'debit' => 0,
                    'kredit' => $penjualan->grand_total,
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();

            // Cek apakah perlu cetak struk atau faktur
            if ($request->has('cetak_format') && $request->cetak_format !== 'none') {
                // Sediakan URL print untuk dibuka di tab baru via JavaScript
                $printUrl = route('penjualan.print', [
                    'id' => $penjualan->id,
                    'format' => $request->cetak_format
                ]);

                // Redirect ke form create dengan parameter print_url untuk dibuka via JavaScript
                return redirect()->route('penjualan.create')
                    ->with('success', 'Transaksi berhasil disimpan dan dicetak')
                    ->with('print_url', $printUrl);
            } else {
                // Redirect kembali ke form create untuk transaksi baru
                return redirect()->route('penjualan.create')
                    ->with('success', 'Transaksi penjualan berhasil ditambahkan');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating penjualan: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $penjualan = Penjualan::with([
            'pasien',
            'user',
            'details.obat',
            'details.satuan',
            'details.lokasi',
            'transaksiAkun'
        ])->findOrFail($id);

        return view('penjualan.show', compact('penjualan'));
    }

    /**
     * Generate PDF receipt for printing
     */
    public function printPdf(string $id, Request $request)
    {
        $penjualan = Penjualan::with([
            'pasien',
            'user',
            'details.obat',
            'details.satuan'
        ])->findOrFail($id);

        $setting = getSetting();
        $format = $request->format ?? '58mm'; // Default to 58mm if not specified

        if ($format === 'a4') {
            // Use A4 paper format
            $pdf = Pdf::setPaper('a4', 'portrait');

            // Configure DomPDF options for better quality
            $pdf->setOption('defaultFont', 'sans-serif');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isFontSubsettingEnabled', true);

            $pdf->loadView('penjualan.print_a4', compact('penjualan', 'setting'));

            // Return the PDF as a download with a filename based on invoice number
            return $pdf->stream("faktur-{$penjualan->no_faktur}.pdf", [
                'Attachment' => false // Set to false to open in browser
            ]);
        } else {
            // Set custom paper size for thermal printer (82mm width)
            // 82mm ≈ 232 points (1 mm ≈ 2.83465 points)
            $customPaper = [0, 0, 232, 800];

            // Set DomPDF options for smaller left/right margins
            $pdf = Pdf::setPaper($customPaper, 'portrait');
            $pdf->setOption('defaultFont', 'sans-serif');
            $pdf->setOption('isRemoteEnabled', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isFontSubsettingEnabled', true);

            // Set margin kiri-kanan lebih kecil (misal 2mm = 5.67pt)
            $pdf->setOption('margin-left', 0);
            $pdf->setOption('margin-right', 0);
            // Margin atas/bawah bisa diatur juga jika perlu
            // $pdf->setOption('margin-top', 5);
            // $pdf->setOption('margin-bottom', 5);

            $pdf->loadView('penjualan.print', compact('penjualan', 'setting'));

            // Return PDF sebagai stream (buka di browser)
            return $pdf->stream("struk-{$penjualan->no_faktur}.pdf", [
                'Attachment' => false
            ]);
        }
    }

    /**
     * Search for products (obat) with stock for Select2 and direct search
     * Group by satuan to show each product-satuan combination as a separate item
     */
    public function searchObat(Request $request)
    {
        try {
            $query = $request->get('q');
            $page = $request->get('page', 1);
            $perPage = 10;

            // Start with a base query with all necessary relationships
            $obatQuery = Obat::with([
                'stok' => function ($q) {
                    $q->where('qty', '>', 0)->orderBy('tanggal_expired', 'asc');
                },
                'satuans.satuan', // Load ObatSatuan with related SatuanObat
                'kategori',
                'golongan',
                'pabrik',
                'lokasi'
            ])
                ->whereHas('stok', function ($q) {
                    $q->where('qty', '>', 0);
                });

            // Check if we're searching by ID (direct selection) or by text
            if (is_numeric($query) && strlen($query) < 5) {
                // Direct search by ID
                $obatQuery->where('id', $query);
            } else {
                // Text search on name and code
                $obatQuery->where(function ($q) use ($query) {
                    $q->where('nama_obat', 'like', "%{$query}%")
                        ->orWhere('kode_obat', 'like', "%{$query}%");
                });
            }

            // Get obats (without pagination yet)
            $obats = $obatQuery->get();

            // New approach: Create a separate result for each obat + satuan combination
            $combinedResults = [];

            foreach ($obats as $obat) {
                // Group stocks by satuan_id
                $stockBySatuan = $obat->stok->groupBy('satuan_id');

                // For each satuan that has stock, create a separate result item
                foreach ($stockBySatuan as $satuanId => $stockItems) {
                    // Skip if no stock
                    if ($stockItems->isEmpty()) continue;

                    // Find the matching ObatSatuan record
                    $obatSatuan = $obat->satuans->firstWhere('satuan_id', $satuanId);
                    if (!$obatSatuan || !$obatSatuan->satuan) continue;

                    // Get the first stock item (FIFO)
                    $firstStock = $stockItems->first();

                    // Calculate total stock for this satuan
                    $satuanTotalStock = $stockItems->sum('qty');

                    // Get the satuan name
                    $satuanName = $obatSatuan->satuan->nama;

                    // Format the text display
                    $text = "{$obat->nama_obat} - {$satuanName} | Stok: {$satuanTotalStock} | " .
                        "Kode: {$obat->kode_obat}";

                    // Create a result object for this combination
                    $combinedResults[] = [
                        // Use a composite ID to make each combination unique
                        'id' => $obat->id . '_' . $satuanId,
                        'text' => $text,
                        'obat' => [
                            'id' => $obat->id,
                            'nama_obat' => $obat->nama_obat,
                            'kode_obat' => $obat->kode_obat,
                            'satuan_id' => $satuanId, // The selected satuan
                            'satuan' => [
                                'id' => $satuanId,
                                'nama_satuan' => $satuanName,
                                'harga_jual' => $firstStock->harga_jual ?? $obatSatuan->harga_jual
                            ],
                            // Include only the first stock item (FIFO)
                            'first_stock' => [
                                'id' => $firstStock->id,
                                'obat_id' => $firstStock->obat_id,
                                'satuan_id' => $firstStock->satuan_id,
                                'lokasi_id' => $firstStock->lokasi_id,
                                'no_batch' => $firstStock->no_batch,
                                'tanggal_expired' => $firstStock->tanggal_expired,
                                'qty' => $firstStock->qty,
                                'harga_jual' => $firstStock->harga_jual,
                                'harga_beli' => $firstStock->harga_beli,
                                'harga' => $firstStock->harga_jual, // Legacy compatibility
                                'lokasi' => $firstStock->lokasi ? [
                                    'id' => $firstStock->lokasi->id,
                                    'nama_lokasi' => $firstStock->lokasi->nama_lokasi
                                ] : null
                            ],
                            'total_stok' => $satuanTotalStock,
                            'kategori' => $obat->kategori ? $obat->kategori->nama : '-',
                            'golongan' => $obat->golongan ? $obat->golongan->nama : '-'
                        ]
                    ];
                }
            }

            // Apply pagination to the combined results
            $count = count($combinedResults);
            $offset = ($page - 1) * $perPage;
            $paginatedResults = array_slice($combinedResults, $offset, $perPage);

            // Format response for Select2
            return response()->json([
                'results' => $paginatedResults,
                'pagination' => [
                    'more' => ($offset + $perPage) < $count
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Search error: " . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat mencari obat',
                'details' => $e->getMessage(),
                'results' => []
            ], 500);
        }
    }

    /**
     * Get detailed stock info for a specific product-satuan combination
     */
    public function getStokDetail(Request $request)
    {
        // Parse the composite ID to get obat_id and satuan_id
        $compositeId = $request->get('obat_id');

        // Check if it's a composite ID (obat_id_satuan_id format)
        if (strpos($compositeId, '_') !== false) {
            list($obatId, $satuanId) = explode('_', $compositeId);
        } else {
            // Fallback to just obat_id if not composite
            $obatId = $compositeId;
            $satuanId = $request->get('satuan_id');
        }

        // Get the ObatSatuan record for this obat and satuan
        $obatSatuan = ObatSatuan::where('obat_id', $obatId)
            ->where('satuan_id', $satuanId)
            ->first();

        // Get the first stock item (FIFO) for this obat and satuan
        $firstStock = Stok::where('obat_satuan_id', $obatSatuan ? $obatSatuan->id : null)
            ->where('qty', '>', 0)
            ->with([
                'obat',
                'lokasi',
                'satuan',
                'obatSatuan' // Include pricing info for this satuan
            ])
            ->orderBy('tanggal_expired', 'asc')
            ->first();

        // If no stock found
        if (!$firstStock) {
            return response()->json([]);
        }

        // Get price from stock's harga_jual, fallback to obatSatuan if needed
        $harga = 0;
        if ($firstStock->harga_jual) {
            $harga = $firstStock->harga_jual;
        } elseif ($firstStock->obatSatuan) {
            $harga = $firstStock->obatSatuan->harga_jual;
        }

        // Format response as a single item
        $result = [
            'id' => $firstStock->id,
            'obat_id' => $firstStock->obat_id,
            'satuan_id' => $firstStock->satuan_id,
            'lokasi_id' => $firstStock->lokasi_id,
            'no_batch' => $firstStock->no_batch,
            'tanggal_expired' => $firstStock->tanggal_expired,
            'qty' => $firstStock->qty,
            'harga' => $harga,
            'lokasi' => $firstStock->lokasi ? [
                'id' => $firstStock->lokasi->id,
                'nama_lokasi' => $firstStock->lokasi->nama_lokasi
            ] : null,
            'satuan' => $firstStock->satuan ? [
                'id' => $firstStock->satuan->id,
                'nama_satuan' => $firstStock->satuan->nama
            ] : null
        ];

        return response()->json([$result]); // Wrap in array for consistency with the previous API
    }

    /**
     * Delete a sales transaction and return medicines to stock.
     *
     * @param string $id Sales ID to be deleted
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            // Find the penjualan record with its details
            $penjualan = Penjualan::with(['details', 'transaksiAkun'])->findOrFail($id);

            // Process each penjualan detail and return items to stock
            foreach ($penjualan->details as $detail) {
                // Get the ObatSatuan record for this obat and satuan
                $obatSatuan = ObatSatuan::where('obat_id', $detail->obat_id)
                    ->where('satuan_id', $detail->satuan_id)
                    ->first();

                // Try to find existing stock with the same batch number and obat_satuan_id
                $stok = Stok::where('no_batch', $detail->no_batch)
                    ->where('obat_satuan_id', $obatSatuan ? $obatSatuan->id : null)
                    ->where('lokasi_id', $detail->lokasi_id)
                    ->first();

                if ($stok) {
                    // Stock exists, increase the quantity
                    $stok->qty += $detail->jumlah;
                    $stok->save();

                    Log::info("Returned {$detail->jumlah} units of obat {$detail->obat_id} batch {$detail->no_batch} back to stock. New qty: {$stok->qty}");
                } else {
                    // Stock doesn't exist, create a new stock entry
                    $newStock = new Stok([
                        'obat_id' => $detail->obat_id,
                        'satuan_id' => $detail->satuan_id,
                        'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null,
                        'lokasi_id' => $detail->lokasi_id,
                        'no_batch' => $detail->no_batch,
                        'tanggal_expired' => $detail->tanggal_expired,
                        'qty' => $detail->jumlah,
                        'harga_beli' => $detail->harga_beli,
                        'harga_jual' => $detail->harga
                    ]);
                    $newStock->save();

                    Log::info("Created new stock entry for {$detail->jumlah} units of obat {$detail->obat_id} batch {$detail->no_batch}");
                }

                // Delete the detail record
                $detail->delete();
            }

            // Delete related accounting transactions
            if ($penjualan->transaksiAkun->count() > 0) {
                foreach ($penjualan->transaksiAkun as $transaksi) {
                    $transaksi->delete();
                }
                Log::info("Deleted {$penjualan->transaksiAkun->count()} accounting transactions for penjualan {$penjualan->no_faktur}");
            }

            // Delete the penjualan record
            $penjualan->delete();

            DB::commit();
            return redirect()->route('penjualan.index')
                ->with('success', 'Penjualan berhasil dihapus dan stok obat telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting penjualan: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus penjualan: ' . $e->getMessage());
        }
    }
}
