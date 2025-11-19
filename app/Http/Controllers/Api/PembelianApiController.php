<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PembelianApiController extends Controller
{
    /**
     * Send online pembelian to offline application
     */
    public function sendOnline(Request $request)
    {
        try {
            // Get pembelian that are marked as online
            $query = Pembelian::with([
                'supplier',
                'details.obat',
                'details.satuan',
                'akunKas'
            ])
                ->where('is_online', true)
                ->orderBy('tanggal_faktur', 'desc')
                ->orderBy('id', 'desc');

            // Optional: filter by date range
            if ($request->has('from_date')) {
                $query->where('tanggal_faktur', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('tanggal_faktur', '<=', $request->to_date);
            }

            // Optional: limit results
            $limit = $request->get('limit', 100);
            $pembelian = $query->limit($limit)->get();

            // Format response
            $data = $pembelian->map(function ($p) {
                return [
                    'id' => $p->id, // This will be stored as server_id on offline
                    'no_po' => $p->no_po,
                    'no_faktur' => $p->no_faktur,
                    'tanggal_faktur' => $p->tanggal_faktur->format('Y-m-d'),
                    'supplier_id' => $p->supplier_id,
                    'jenis' => $p->jenis,
                    'akun_kas_id' => $p->akun_kas_id,
                    'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo ? $p->tanggal_jatuh_tempo->format('Y-m-d') : null,
                    'subtotal' => $p->subtotal,
                    'diskon_total' => $p->diskon_total,
                    'ppn_total' => $p->ppn_total,
                    'grand_total' => $p->grand_total,
                    'status_pembayaran' => $p->status_pembayaran,
                    'user_id' => $p->user_id,
                    'details' => $p->details->map(function ($d) {
                        return [
                            'obat_id' => $d->obat_id,
                            'obat_satuan_id' => $d->obat_satuan_id,
                            'satuan_id' => $d->satuan_id,
                            'jumlah' => $d->jumlah,
                            'harga_beli' => $d->harga_beli,
                            'subtotal' => $d->subtotal,
                            'diskon_persen' => $d->diskon_persen,
                            'diskon_nominal' => $d->diskon_nominal,
                            'hpp_per_unit' => $d->hpp_per_unit,
                            'hna_ppn_per_unit' => $d->hna_ppn_per_unit,
                            'margin_jual_persen' => $d->margin_jual_persen,
                            'harga_jual_per_unit' => $d->harga_jual_per_unit,
                            'no_batch' => $d->no_batch,
                            'tanggal_expired' => $d->tanggal_expired,
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
            Log::error('Error sending online pembelian', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
