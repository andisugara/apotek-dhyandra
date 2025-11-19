<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\ObatSatuan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PenjualanApiController extends Controller
{
    /**
     * Health check endpoint
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Server is online',
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Receive penjualan from offline application
     */
    public function receive(Request $request)
    {
        try {
            // Validasi data
            $validator = Validator::make($request->all(), [
                'offline_id' => 'required|integer',
                'no_faktur' => 'required|string',
                'tanggal_penjualan' => 'required|date',
                'pasien_id' => 'nullable|exists:pasien,id',
                'jenis' => 'required|in:TUNAI,NON_TUNAI',
                'keterangan' => 'nullable|string',
                'subtotal' => 'required|numeric',
                'diskon_total' => 'nullable|numeric',
                'ppn_total' => 'nullable|numeric',
                'tuslah_total' => 'nullable|numeric',
                'embalase_total' => 'nullable|numeric',
                'grand_total' => 'required|numeric',
                'bayar' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'detail' => 'required|array',
                'detail.*.obat_id' => 'required|exists:obat,id',
                'detail.*.satuan_id' => 'required|exists:satuan_obat,id',
                'detail.*.jumlah' => 'required|integer|min:1',
                'detail.*.harga' => 'required|numeric',
                'detail.*.subtotal' => 'required|numeric',
                'detail.*.diskon' => 'nullable|numeric',
                'detail.*.ppn' => 'nullable|numeric',
                'detail.*.tuslah' => 'nullable|numeric',
                'detail.*.embalase' => 'nullable|numeric',
                'detail.*.total' => 'required|numeric',
                'detail.*.no_batch' => 'nullable|string',
                'detail.*.lokasi_id' => 'nullable|exists:lokasi_obat,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Cek duplikasi berdasarkan no_faktur
            $existing = Penjualan::where('no_faktur', $request->no_faktur)->first();
            if ($existing) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Penjualan dengan no_faktur ini sudah ada',
                    'data' => ['id' => $existing->id]
                ], 409);
            }

            // Buat penjualan baru
            $penjualan = Penjualan::create([
                'no_faktur' => $request->no_faktur,
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'pasien_id' => $request->pasien_id,
                'jenis' => $request->jenis ?? 'TUNAI',
                'keterangan' => $request->keterangan ?? null,
                'subtotal' => $request->subtotal,
                'diskon_total' => $request->diskon_total ?? 0,
                'ppn_total' => $request->ppn_total ?? 0,
                'tuslah_total' => $request->tuslah_total ?? 0,
                'embalase_total' => $request->embalase_total ?? 0,
                'grand_total' => $request->grand_total,
                'bayar' => $request->bayar,
                'kembalian' => $request->kembalian,
                'is_online' => true, // Mark as online
                'server_id' => null, // This IS the server
                'user_id' => $request->user_id ?? auth()->id() ?? 1,
            ]);

            // Create detail dan update stok
            foreach ($request->detail as $item) {
                // Get the obat info
                $obat = Obat::findOrFail($item['obat_id']);

                // Create penjualan detail
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $item['obat_id'],
                    'satuan_id' => $item['satuan_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_beli' => 0, // Will be updated from stock
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                    'diskon' => $item['diskon'] ?? 0,
                    'ppn' => $item['ppn'] ?? 0,
                    'tuslah' => $item['tuslah'] ?? 0,
                    'embalase' => $item['embalase'] ?? 0,
                    'total' => $item['total'],
                    'no_batch' => $item['no_batch'] ?? '',
                    'lokasi_id' => $item['lokasi_id'] ?? 1
                ]);

                // Get the ObatSatuan record
                $obatSatuan = ObatSatuan::where('obat_id', $item['obat_id'])
                    ->where('satuan_id', $item['satuan_id'])
                    ->first();

                // FIFO Stock Reduction Logic
                $availableStocks = Stok::where('obat_satuan_id', $obatSatuan ? $obatSatuan->id : null)
                    ->where('qty', '>', 0)
                    ->orderBy('tanggal_expired', 'asc') // FIFO: oldest expiry first
                    ->get();

                $remainingQty = $item['jumlah'];
                $firstBatch = null;
                $weightedHargaBeli = 0;
                $totalQtyUsed = 0;

                // Loop through stocks and reduce quantity following FIFO
                foreach ($availableStocks as $stok) {
                    if ($remainingQty <= 0) break;

                    // Store first batch info
                    if (!$firstBatch) {
                        $firstBatch = $stok;
                    }

                    $qtyToTake = min($remainingQty, $stok->qty);

                    // Calculate weighted average harga_beli
                    $weightedHargaBeli += ($stok->harga_beli * $qtyToTake);
                    $totalQtyUsed += $qtyToTake;

                    // Reduce stock
                    $stok->qty -= $qtyToTake;
                    $stok->save();

                    $remainingQty -= $qtyToTake;
                }

                // Check if we have enough stock
                if ($remainingQty > 0) {
                    throw new \Exception("Stok tidak mencukupi untuk {$obat->nama_obat}. Kurang {$remainingQty} unit.");
                }

                // Calculate average harga_beli
                $avgHargaBeli = $totalQtyUsed > 0 ? $weightedHargaBeli / $totalQtyUsed : 0;

                // Update detail with batch info and average harga_beli
                $penjualanDetail->update([
                    'tanggal_expired' => $firstBatch ? $firstBatch->tanggal_expired : null,
                    'harga_beli' => $avgHargaBeli,
                    'no_batch' => $firstBatch ? $firstBatch->no_batch : ($item['no_batch'] ?? '')
                ]);
            }

            DB::commit();

            Log::info('Penjualan received from offline app', [
                'offline_id' => $request->offline_id,
                'server_id' => $penjualan->id,
                'no_faktur' => $penjualan->no_faktur
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil diterima',
                'data' => [
                    'id' => $penjualan->id,
                    'no_faktur' => $penjualan->no_faktur,
                    'offline_id' => $request->offline_id
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving penjualan from offline', [
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
     * Send online penjualan to offline application
     */
    public function sendOnline(Request $request)
    {
        try {
            // Get penjualan that are marked as online
            // Optionally filter by date range
            $query = Penjualan::with(['details.obat', 'pasien'])
                ->where('is_online', true)
                ->orderBy('tanggal_penjualan', 'desc')
                ->orderBy('id', 'desc');

            // Optional: filter by date range
            if ($request->has('from_date')) {
                $query->where('tanggal_penjualan', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('tanggal_penjualan', '<=', $request->to_date);
            }

            // Optional: limit results
            $limit = $request->get('limit', 100);
            $penjualan = $query->limit($limit)->get();

            // Format response
            $data = $penjualan->map(function ($p) {
                return [
                    'id' => $p->id, // This will be stored as server_id on offline
                    'no_faktur' => $p->no_faktur,
                    'tanggal_penjualan' => $p->tanggal_penjualan,
                    'pasien_id' => $p->pasien_id,
                    'jenis' => $p->jenis,
                    'keterangan' => $p->keterangan,
                    'subtotal' => $p->subtotal,
                    'diskon_total' => $p->diskon_total,
                    'ppn_total' => $p->ppn_total,
                    'tuslah_total' => $p->tuslah_total,
                    'embalase_total' => $p->embalase_total,
                    'grand_total' => $p->grand_total,
                    'bayar' => $p->bayar,
                    'kembalian' => $p->kembalian,
                    'user_id' => $p->user_id,
                    'details' => $p->details->map(function ($d) {
                        return [
                            'obat_id' => $d->obat_id,
                            'satuan_id' => $d->satuan_id,
                            'jumlah' => $d->jumlah,
                            'harga_beli' => $d->harga_beli ?? 0,
                            'harga' => $d->harga,
                            'subtotal' => $d->subtotal,
                            'diskon' => $d->diskon,
                            'ppn' => $d->ppn,
                            'tuslah' => $d->tuslah,
                            'embalase' => $d->embalase,
                            'total' => $d->total,
                            'no_batch' => $d->no_batch,
                            'lokasi_id' => $d->lokasi_id ?? 1,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending online penjualan', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
