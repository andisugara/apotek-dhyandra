<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                'tanggal' => 'required|date',
                'pasien_id' => 'nullable|exists:pasiens,id',
                'nama_pasien' => 'required|string',
                'dokter' => 'nullable|string',
                'total' => 'required|numeric',
                'bayar' => 'required|numeric',
                'kembalian' => 'required|numeric',
                'detail' => 'required|array',
                'detail.*.obat_id' => 'required|exists:obats,id',
                'detail.*.qty' => 'required|integer|min:1',
                'detail.*.harga' => 'required|numeric',
                'detail.*.diskon_persen' => 'nullable|numeric',
                'detail.*.diskon_rp' => 'nullable|numeric',
                'detail.*.total' => 'required|numeric',
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
                'tanggal' => $request->tanggal,
                'pasien_id' => $request->pasien_id,
                'nama_pasien' => $request->nama_pasien,
                'dokter' => $request->dokter,
                'total' => $request->total,
                'bayar' => $request->bayar,
                'kembalian' => $request->kembalian,
                'is_online' => true, // Mark as online
                'server_id' => null, // This IS the server
                'user_id' => $request->user_id ?? auth()->id() ?? 1,
            ]);

            // Create detail dan update stok
            foreach ($request->detail as $item) {
                // Create penjualan detail
                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $item['obat_id'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'] ?? 'PCS',
                    'harga' => $item['harga'],
                    'diskon_persen' => $item['diskon_persen'] ?? 0,
                    'diskon_rp' => $item['diskon_rp'] ?? 0,
                    'total' => $item['total'],
                ]);

                // Update stok dengan FIFO
                $this->updateStokFIFO(
                    $item['obat_id'],
                    $item['qty'],
                    $item['lokasi_id'] ?? 1
                );
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
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc');

            // Optional: filter by date range
            if ($request->has('from_date')) {
                $query->where('tanggal', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('tanggal', '<=', $request->to_date);
            }

            // Optional: limit results
            $limit = $request->get('limit', 100);
            $penjualan = $query->limit($limit)->get();

            // Format response
            $data = $penjualan->map(function ($p) {
                return [
                    'id' => $p->id, // This will be stored as server_id on offline
                    'no_faktur' => $p->no_faktur,
                    'tanggal' => $p->tanggal,
                    'pasien_id' => $p->pasien_id,
                    'nama_pasien' => $p->nama_pasien,
                    'dokter' => $p->dokter,
                    'total' => $p->total,
                    'bayar' => $p->bayar,
                    'kembalian' => $p->kembalian,
                    'user_id' => $p->user_id,
                    'detail' => $p->details->map(function ($d) {
                        return [
                            'obat_id' => $d->obat_id,
                            'qty' => $d->qty,
                            'satuan' => $d->satuan,
                            'harga' => $d->harga,
                            'diskon_persen' => $d->diskon_persen,
                            'diskon_rp' => $d->diskon_rp,
                            'total' => $d->total,
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

    /**
     * Update stok using FIFO method
     * (Copied from PenjualanController)
     */
    private function updateStokFIFO($obatId, $qty, $lokasiId)
    {
        $remainingQty = $qty;

        // Get available stock sorted by oldest first (FIFO)
        $stokItems = Stok::where('obat_id', $obatId)
            ->where('lokasi_id', $lokasiId)
            ->where('qty', '>', 0)
            ->orderBy('tanggal_ed', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // If no stock at specified location, try other locations
        if ($stokItems->isEmpty() || $stokItems->sum('qty') < $qty) {
            $stokItems = Stok::where('obat_id', $obatId)
                ->where('qty', '>', 0)
                ->orderBy('tanggal_ed', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();
        }

        foreach ($stokItems as $stok) {
            if ($remainingQty <= 0) {
                break;
            }

            if ($stok->qty >= $remainingQty) {
                // Stock in this batch is enough
                $stok->qty -= $remainingQty;
                $stok->save();
                $remainingQty = 0;
            } else {
                // Use all stock from this batch and continue
                $remainingQty -= $stok->qty;
                $stok->qty = 0;
                $stok->save();
            }
        }

        if ($remainingQty > 0) {
            Log::warning('Insufficient stock for obat_id: ' . $obatId, [
                'requested' => $qty,
                'remaining' => $remainingQty
            ]);
        }
    }
}
