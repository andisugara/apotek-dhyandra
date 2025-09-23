<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Supplier;
use App\Models\Obat;
use App\Models\ObatSatuan;
use App\Models\SatuanObat;
use App\Models\Stok;
use App\Models\Akun;
use App\Models\TransaksiAkun;
use App\Models\LokasiObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Pembelian::with(['supplier', 'akunKas', 'user'])
                ->select('*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('supplier_nama', function ($row) {
                    return $row->supplier ? $row->supplier->nama : '-';
                })
                ->addColumn('jenis_badge', function ($row) {
                    $class = '';
                    switch ($row->jenis) {
                        case 'TUNAI':
                            $class = 'badge-light-success';
                            break;
                        case 'HUTANG':
                            $class = 'badge-light-warning';
                            break;
                        case 'KONSINYASI':
                            $class = 'badge-light-info';
                            break;
                    }
                    return '<span class="badge ' . $class . '">' . $row->jenis . '</span>';
                })
                ->addColumn('status_jatuh_tempo', function ($row) {
                    if ($row->jenis !== 'HUTANG' || !$row->tanggal_jatuh_tempo) {
                        return '-';
                    }

                    $status = $row->status_jatuh_tempo;
                    $class = '';

                    switch ($status) {
                        case 'TERLAMBAT':
                            $class = 'badge-light-danger';
                            break;
                        case 'JATUH TEMPO HARI INI':
                            $class = 'badge-light-warning';
                            break;
                        case 'MENDEKATI JATUH TEMPO':
                            $class = 'badge-light-info';
                            break;
                        default:
                            $class = 'badge-light-success';
                    }

                    return '<span class="badge ' . $class . '">' . $status . '</span>';
                })
                ->addColumn('status_pembayaran_formatted', function ($row) {
                    if ($row->jenis !== 'HUTANG') {
                        return $row->jenis === 'TUNAI' ?
                            '<span class="badge badge-success">LUNAS</span>' :
                            '<span class="badge badge-info">KONSINYASI</span>';
                    }

                    return $row->status_pembayaran_formatted;
                })
                ->addColumn('tanggal_faktur_formatted', function ($row) {
                    return $row->tanggal_faktur->format('d/m/Y');
                })
                ->addColumn('tanggal_jatuh_tempo_formatted', function ($row) {
                    return $row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->format('d/m/Y') : '-';
                })
                ->addColumn('grand_total_formatted', function ($row) {
                    return 'Rp ' . $row->formatted_grand_total;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembelian.show', $row->id) . '" class="btn btn-sm btn-info">Detail</a> ';
                    $actionBtn .= '<a href="' . route('pembelian.edit', $row->id) . '" class="btn btn-sm btn-primary">Edit</a> ';
                    $actionBtn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Hapus</button>';
                    return $actionBtn;
                })
                ->rawColumns(['jenis_badge', 'status_jatuh_tempo', 'status_pembayaran_formatted', 'action'])
                ->make(true);
        }

        return view('pembelian.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('status', '1')->orderBy('nama')->get();
        $akuns = Akun::where('status', '1')->orderBy('nama')->get();
        $obats = Obat::with(['satuans.satuan'])->where('is_active', '1')->orderBy('nama_obat')->get();
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();

        return view('pembelian.create', compact('suppliers', 'akuns', 'obats', 'lokasis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate main pembelian data
        $rules = [
            'no_faktur' => 'required|string|max:255|unique:pembelian,no_faktur',
            'tanggal_faktur' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'jenis' => 'required|in:TUNAI,HUTANG,KONSINYASI',
            'detail' => 'required|array|min:1',
            'detail.*.obat_id' => 'required|exists:obat,id',
            'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.harga_beli' => 'required|numeric|min:0',
            'detail.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
            'detail.*.margin_jual_persen' => 'nullable|numeric|min:0',
            'detail.*.harga_jual_per_unit' => 'required|numeric|min:0',
            'detail.*.no_batch' => 'required|string',
            'detail.*.tanggal_expired' => 'required|date|after:today',
            'detail.*.lokasi_id' => 'required|exists:lokasi_obat,id',
        ];

        // Add conditional validation based on jenis
        if ($request->jenis === 'TUNAI') {
            $rules['akun_kas_id'] = 'required|exists:akun,id';
        } elseif ($request->jenis === 'HUTANG') {
            $rules['tanggal_jatuh_tempo'] = 'required|date|after_or_equal:tanggal_faktur';
        }

        // Validate the request
        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Create pembelian
            $pembelian = Pembelian::create([
                'no_po' => $request->no_po,
                'no_faktur' => $validated['no_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jenis' => $validated['jenis'],
                'akun_kas_id' => $request->akun_kas_id,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'subtotal' => 0, // Will be calculated from details
                'diskon_total' => 0, // Will be calculated from details
                'ppn_total' => $request->ppn_total ?? 0,
                'grand_total' => 0, // Will be calculated from details
                'user_id' => Auth::id(),
            ]);

            $subtotal = 0;
            $diskonTotal = 0;
            $grandTotal = 0;


            // Process detail items
            foreach ($validated['detail'] as $detail) {
                // Calculate values
                $hargaBeli = floatval(str_replace([',', '.'], '', $detail['harga_beli']));
                $jumlah = intval($detail['jumlah']);
                $subtotalItem = $hargaBeli * $jumlah;

                $diskonPersen = floatval($detail['diskon_persen'] ?? 0);
                $diskonNominal = ($diskonPersen / 100) * $subtotalItem;

                $totalItem = $subtotalItem - $diskonNominal;
                $hppPerUnit = ($subtotalItem - $diskonNominal) / $jumlah;

                // HNA+PPN is the same as purchase price for simplicity
                $hnaPpnPerUnit = $hargaBeli;

                $marginJualPersen = floatval($detail['margin_jual_persen'] ?? 10);
                $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                $hargaJualPerUnit = $hppPerUnit + $marginJualNominal;

                // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                $ppnPersen = floatval($request->ppn_total ?? 0);
                $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                // Hitung ulang HPP dengan PPN
                $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                // Hitung ulang harga jual berdasarkan HPP baru
                // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));

                $obatSatuan = ObatSatuan::where('obat_id', $detail['obat_id'])
                    ->where('satuan_id', $detail['satuan_id'])
                    ->first();

                // Create detail record
                $pembelianDetail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'obat_id' => $detail['obat_id'],
                    'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null,
                    'satuan_id' => $detail['satuan_id'],
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotalItem,
                    'diskon_persen' => $diskonPersen,
                    'diskon_nominal' => $diskonNominal,
                    'hpp_per_unit' => $hppPerUnit,
                    'hna_ppn_per_unit' => $hnaPpnPerUnit,
                    'margin_jual_persen' => $marginJualPersen,
                    'harga_jual_per_unit' => $hargaJualPerUnit,
                    'no_batch' => $detail['no_batch'],
                    'tanggal_expired' => $detail['tanggal_expired'],
                    'total' => $totalItem
                ]);

                // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                // Hitung PPN untuk item ini
                $ppnPersen = floatval($request->ppn_total ?? 0);
                $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                // Hitung ulang HPP dengan PPN
                $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                // Hitung ulang harga jual berdasarkan HPP baru
                // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));

                // Create or update stock
                Stok::create([
                    'obat_id' => $detail['obat_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null, // Set obat_satuan_id
                    'lokasi_id' => $detail['lokasi_id'],
                    'no_batch' => $detail['no_batch'],
                    'tanggal_expired' => $detail['tanggal_expired'],
                    'qty' => $jumlah,
                    'qty_awal' => $jumlah,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'harga_beli' => $hppPerUnit, // Menggunakan HPP sebagai harga beli
                    'harga_jual' => $hargaJualPerUnit
                ]);

                if ($obatSatuan) {
                    $obatSatuan->update([
                        'harga_beli' => $hppPerUnit,
                        'profit_persen' => $marginJualPersen,
                        'harga_jual' => $hargaJualPerUnit
                    ]);
                } else {
                    ObatSatuan::create([
                        'obat_id' => $detail['obat_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'harga_beli' => $hppPerUnit,
                        'diskon_persen' => 0,
                        'profit_persen' => $marginJualPersen,
                        'harga_jual' => $hargaJualPerUnit
                    ]);
                }

                // Add to totals
                $subtotal += $subtotalItem;
                $diskonTotal += $diskonNominal;
                $grandTotal += $totalItem;
            }

            // Add PPN if applicable
            $ppnPersen = floatval($request->ppn_total ?? 0);
            $ppnNominal = ($ppnPersen / 100) * ($subtotal - $diskonTotal);
            $grandTotal += $ppnNominal;

            // Update pembelian with calculated totals
            $pembelian->update([
                'subtotal' => $subtotal,
                'diskon_total' => $diskonTotal,
                'ppn_total' => $ppnNominal, // Simpan nilai PPN dalam Rupiah
                'grand_total' => $grandTotal
            ]);

            // Create accounting transaction
            if ($pembelian->jenis === 'TUNAI') {
                // For cash purchases, create a debit transaction
                TransaksiAkun::create([
                    'akun_id' => $pembelian->akun_kas_id,
                    'tanggal' => $pembelian->tanggal_faktur,
                    'kode_referensi' => 'PB-' . $pembelian->id,
                    'tipe_referensi' => 'PEMBELIAN',
                    'referensi_id' => $pembelian->id,
                    'deskripsi' => 'Pembelian obat dengan faktur ' . $pembelian->no_faktur,
                    'debit' => $grandTotal, // For purchases, debit the account
                    'kredit' => 0,
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil ditambahkan');
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
        $pembelian = Pembelian::with(['supplier', 'akunKas', 'user', 'details.obat', 'details.satuan', 'transaksiAkun'])
            ->findOrFail($id);

        return view('pembelian.show', compact('pembelian'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pembelian = Pembelian::with(['supplier', 'akunKas', 'details.obat', 'details.satuan', 'details.stok'])
            ->findOrFail($id);

        $suppliers = Supplier::where('status', '1')->orderBy('nama')->get();
        $akuns = Akun::where('status', '1')->orderBy('nama')->get();
        $obats = Obat::with(['satuans.satuan'])->where('is_active', '1')->orderBy('nama_obat')->get();
        $lokasis = LokasiObat::where('is_active', '1')->orderBy('nama')->get();

        return view('pembelian.edit', compact('pembelian', 'suppliers', 'akuns', 'obats', 'lokasis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Validate main pembelian data
        $rules = [
            'no_faktur' => 'required|string|max:255|unique:pembelian,no_faktur,' . $id,
            'tanggal_faktur' => 'required|date',
            'supplier_id' => 'required|exists:suppliers,id',
            'jenis' => 'required|in:TUNAI,HUTANG,KONSINYASI',
            'detail' => 'required|array|min:1',
            'detail.*.id' => 'nullable|exists:pembelian_detail,id',
            'detail.*.obat_id' => 'required|exists:obat,id',
            'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
            'detail.*.jumlah' => 'required|integer|min:1',
            'detail.*.harga_beli' => 'required|numeric|min:0',
            'detail.*.diskon_persen' => 'nullable|numeric|min:0|max:100',
            'detail.*.margin_jual_persen' => 'nullable|numeric|min:0',
            'detail.*.harga_jual_per_unit' => 'required|numeric|min:0',
            'detail.*.no_batch' => 'required|string',
            'detail.*.tanggal_expired' => 'required|date|after:today',
            'detail.*.lokasi_id' => 'required|exists:lokasi_obat,id',
        ];

        // Add conditional validation based on jenis
        if ($request->jenis === 'TUNAI') {
            $rules['akun_kas_id'] = 'required|exists:akun,id';
        } elseif ($request->jenis === 'HUTANG') {
            $rules['tanggal_jatuh_tempo'] = 'required|date|after_or_equal:tanggal_faktur';
        }

        // Validate the request
        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Update pembelian
            $pembelian->update([
                'no_po' => $request->no_po,
                'no_faktur' => $validated['no_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'supplier_id' => $validated['supplier_id'],
                'jenis' => $validated['jenis'],
                'akun_kas_id' => $request->akun_kas_id,
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'ppn_total' => $request->ppn_total ?? 0,
            ]);

            // Get existing detail ids
            $existingDetailIds = $pembelian->details->pluck('id')->toArray();
            $updatedDetailIds = [];

            $subtotal = 0;
            $diskonTotal = 0;
            $grandTotal = 0;
            // Process detail items
            foreach ($validated['detail'] as $detail) {
                // Calculate values
                $hargaBeli = floatval(str_replace([',', '.'], '', $detail['harga_beli']));
                $jumlah = intval($detail['jumlah']);
                $subtotalItem = $hargaBeli * $jumlah;

                $diskonPersen = floatval($detail['diskon_persen'] ?? 0);
                $diskonNominal = ($diskonPersen / 100) * $subtotalItem;

                $totalItem = $subtotalItem - $diskonNominal;
                $hppPerUnit = ($subtotalItem - $diskonNominal) / $jumlah;

                // HNA+PPN is the same as purchase price for simplicity
                $hnaPpnPerUnit = $hargaBeli;

                $marginJualPersen = floatval($detail['margin_jual_persen'] ?? 10);
                // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));



                // Create or update detail record
                if (isset($detail['id']) && $detail['id']) {
                    $pembelianDetail = PembelianDetail::find($detail['id']);
                    if ($pembelianDetail && $pembelianDetail->pembelian_id == $pembelian->id) {
                        // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                        $ppnPersen = floatval($request->ppn_total ?? 0);
                        $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                        $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                        // Hitung ulang HPP dengan PPN
                        $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                        // Hitung ulang harga jual berdasarkan HPP baru
                        // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                        $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));

                        $obatSatuan = ObatSatuan::where('obat_id', $detail['obat_id'])
                            ->where('satuan_id', $detail['satuan_id'])
                            ->first();
                        // Update existing detail
                        $pembelianDetail->update([
                            'obat_id' => $detail['obat_id'],
                            'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null,
                            'satuan_id' => $detail['satuan_id'],
                            'jumlah' => $jumlah,
                            'harga_beli' => $hargaBeli,
                            'subtotal' => $subtotalItem,
                            'diskon_persen' => $diskonPersen,
                            'diskon_nominal' => $diskonNominal,
                            'hpp_per_unit' => $hppPerUnit,
                            'hna_ppn_per_unit' => $hnaPpnPerUnit,
                            'margin_jual_persen' => $marginJualPersen,
                            'harga_jual_per_unit' => $hargaJualPerUnit,
                            'no_batch' => $detail['no_batch'],
                            'tanggal_expired' => $detail['tanggal_expired'],
                            'total' => $totalItem
                        ]);

                        // Update associated stock
                        $stok = Stok::where('pembelian_detail_id', $pembelianDetail->id)->first();
                        if ($stok) {
                            // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                            $ppnPersen = floatval($request->ppn_total ?? 0);
                            $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                            $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                            // Hitung ulang HPP dengan PPN
                            $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                            // Hitung ulang harga jual berdasarkan HPP baru
                            // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                            $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));

                            $stok->update([
                                'obat_id' => $detail['obat_id'],
                                'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null, // Set obat_satuan_id
                                'satuan_id' => $detail['satuan_id'],
                                'lokasi_id' => $detail['lokasi_id'],
                                'no_batch' => $detail['no_batch'],
                                'tanggal_expired' => $detail['tanggal_expired'],
                                'qty' => $jumlah,
                                'qty_awal' => $jumlah,
                                'harga_beli' => $hppPerUnit, // Menggunakan HPP sebagai harga beli
                                'harga_jual' => $hargaJualPerUnit
                            ]);
                        }

                        $updatedDetailIds[] = $pembelianDetail->id;
                    }
                } else {
                    // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                    $ppnPersen = floatval($request->ppn_total ?? 0);
                    $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                    $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                    // Hitung ulang HPP dengan PPN
                    $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                    // Hitung ulang harga jual berdasarkan HPP baru
                    // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                    $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));


                    $obatSatuan = ObatSatuan::where('obat_id', $detail['obat_id'])
                        ->where('satuan_id', $detail['satuan_id'])
                        ->first();
                    // Create new detail
                    $pembelianDetail = PembelianDetail::create([
                        'pembelian_id' => $pembelian->id,
                        'obat_id' => $detail['obat_id'],
                        'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null,
                        'satuan_id' => $detail['satuan_id'],
                        'jumlah' => $jumlah,
                        'harga_beli' => $hargaBeli,
                        'subtotal' => $subtotalItem,
                        'diskon_persen' => $diskonPersen,
                        'diskon_nominal' => $diskonNominal,
                        'hpp_per_unit' => $hppPerUnit,
                        'hna_ppn_per_unit' => $hnaPpnPerUnit,
                        'margin_jual_persen' => $marginJualPersen,
                        'harga_jual_per_unit' => $hargaJualPerUnit,
                        'no_batch' => $detail['no_batch'],
                        'tanggal_expired' => $detail['tanggal_expired'],
                        'total' => $totalItem
                    ]);

                    // Tambahkan perhitungan untuk HPP setelah menambahkan PPN
                    $ppnPersen = floatval($request->ppn_total ?? 0);
                    $ppnNominalPerItem = ($ppnPersen / 100) * $totalItem;
                    $totalDenganPPN = $totalItem + $ppnNominalPerItem;

                    // Hitung ulang HPP dengan PPN
                    $hppPerUnit = $jumlah > 0 ? $totalDenganPPN / $jumlah : 0;

                    // Hitung ulang harga jual berdasarkan HPP baru
                    // $marginJualNominal = ($marginJualPersen / 100) * $hppPerUnit;
                    $hargaJualPerUnit = floatval(str_replace([',', '.'], '', $detail['harga_jual_per_unit'] ?? 0));


                    // Create new stock
                    Stok::create([
                        'obat_id' => $detail['obat_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'obat_satuan_id' => $obatSatuan ? $obatSatuan->id : null, // Set obat_satuan_id
                        'lokasi_id' => $detail['lokasi_id'],
                        'no_batch' => $detail['no_batch'],
                        'tanggal_expired' => $detail['tanggal_expired'],
                        'qty' => $jumlah,
                        'qty_awal' => $jumlah,
                        'pembelian_detail_id' => $pembelianDetail->id,
                        'harga_beli' => $hppPerUnit, // Menggunakan HPP sebagai harga beli
                        'harga_jual' => $hargaJualPerUnit
                    ]);

                    $updatedDetailIds[] = $pembelianDetail->id;
                }

                if ($obatSatuan) {
                    $obatSatuan->update([
                        'harga_beli' => $hppPerUnit,
                        'profit_persen' => $marginJualPersen,
                        'harga_jual' => $hargaJualPerUnit
                    ]);
                } else {
                    ObatSatuan::create([
                        'obat_id' => $detail['obat_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'harga_beli' => $hppPerUnit,
                        'diskon_persen' => 0,
                        'profit_persen' => $marginJualPersen,
                        'harga_jual' => $hargaJualPerUnit
                    ]);
                }

                // Add to totals
                $subtotal += $subtotalItem;
                $diskonTotal += $diskonNominal;
                $grandTotal += $totalItem;
            }

            // Delete details that are not in the updated list
            $detailsToDelete = array_diff($existingDetailIds, $updatedDetailIds);
            if (!empty($detailsToDelete)) {
                foreach ($detailsToDelete as $detailId) {
                    $detail = PembelianDetail::find($detailId);
                    if ($detail) {
                        // Delete associated stock
                        Stok::where('pembelian_detail_id', $detail->id)->delete();

                        // Delete detail
                        $detail->delete();
                    }
                }
            }

            // Add PPN if applicable
            $ppnPersen = floatval($request->ppn_total ?? 0);
            $ppnNominal = ($ppnPersen / 100) * ($subtotal - $diskonTotal);
            $grandTotal += $ppnNominal;

            // Update pembelian with calculated totals
            $pembelian->update([
                'subtotal' => $subtotal,
                'diskon_total' => $diskonTotal,
                'ppn_total' => $ppnNominal, // Simpan nilai PPN dalam Rupiah
                'grand_total' => $grandTotal
            ]);

            // Update accounting transaction if needed
            if ($pembelian->jenis === 'TUNAI') {
                // Delete existing transactions
                TransaksiAkun::where('referensi_id', $pembelian->id)
                    ->where('tipe_referensi', 'PEMBELIAN')
                    ->delete();

                // Create new transaction
                TransaksiAkun::create([
                    'akun_id' => $pembelian->akun_kas_id,
                    'tanggal' => $pembelian->tanggal_faktur,
                    'kode_referensi' => 'PB-' . $pembelian->id,
                    'tipe_referensi' => 'PEMBELIAN',
                    'referensi_id' => $pembelian->id,
                    'deskripsi' => 'Pembelian obat dengan faktur ' . $pembelian->no_faktur,
                    'debit' => $grandTotal, // For purchases, debit the account
                    'kredit' => 0,
                    'user_id' => Auth::id()
                ]);
            } else {
                // Delete any existing transaction if payment type changed
                TransaksiAkun::where('referensi_id', $pembelian->id)
                    ->where('tipe_referensi', 'PEMBELIAN')
                    ->delete();
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil diperbarui');
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
        $pembelian = Pembelian::findOrFail($id);

        DB::beginTransaction();
        try {
            // Delete associated stock records
            foreach ($pembelian->details as $detail) {
                Stok::where('pembelian_detail_id', $detail->id)->delete();
            }

            // Delete transaction records
            TransaksiAkun::where('referensi_id', $pembelian->id)
                ->where('tipe_referensi', 'PEMBELIAN')
                ->delete();

            // Delete details (cascade will handle this)
            $pembelian->delete();

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get all obat satuans for a specific obat
     */
    public function getObatSatuans($id)
    {
        $obat = Obat::with(['satuans.satuan'])->findOrFail($id);
        return response()->json($obat->satuans);
    }

    /**
     * Update payment status for a purchase
     */
    public function updateStatus(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        // Validate request
        $validated = $request->validate([
            'status_pembayaran' => 'required|in:BELUM,SEBAGIAN,LUNAS',
            'akun_kas_id' => 'required_if:status_pembayaran,SEBAGIAN,LUNAS|exists:akun,id',
        ]);

        DB::beginTransaction();
        try {
            // Update the payment status
            $oldStatus = $pembelian->status_pembayaran;
            $newStatus = $validated['status_pembayaran'];

            // Use the model method we just created
            $success = $pembelian->updateStatusPembayaran($newStatus);

            if (!$success) {
                return redirect()->back()->with('error', 'Gagal memperbarui status pembayaran.');
            }

            // If updating to LUNAS or SEBAGIAN, create accounting transaction
            if (($newStatus === 'LUNAS' || $newStatus === 'SEBAGIAN') && $request->has('akun_kas_id')) {
                // Create transaction for the payment
                TransaksiAkun::create([
                    'akun_id' => $request->akun_kas_id,
                    'tanggal' => now(),
                    'kode_referensi' => 'PB-PAY-' . $pembelian->id,
                    'tipe_referensi' => 'PEMBELIAN',
                    'referensi_id' => $pembelian->id,
                    'deskripsi' => 'Pembayaran hutang untuk faktur ' . $pembelian->no_faktur . ' (' .
                        ($newStatus === 'LUNAS' ? 'LUNAS' : 'SEBAGIAN') . ')',
                    'debit' => $pembelian->grand_total, // For debt payments, debit the account
                    'kredit' => 0,
                    'user_id' => Auth::id()
                ]);
            }

            DB::commit();

            // Show appropriate message
            if ($oldStatus !== $newStatus) {
                return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui menjadi ' . $newStatus);
            }

            return redirect()->back()->with('info', 'Tidak ada perubahan pada status pembayaran');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
