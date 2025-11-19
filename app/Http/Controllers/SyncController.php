<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    protected $serverUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->serverUrl = config('app.master_server_url');
        $this->apiToken = config('app.master_api_token');
    }

    /**
     * Tampilkan halaman sync
     */
    public function index()
    {
        $offlineCount = Penjualan::offline()->count();
        $onlineCount = Penjualan::online()->count();

        return view('sync.index', compact('offlineCount', 'onlineCount'));
    }

    /**
     * PUSH penjualan offline ke server
     */
    public function push()
    {
        try {
            // Ambil semua penjualan yang is_online = false
            $offlinePenjualan = Penjualan::with(['details', 'pasien'])
                ->where('is_online', false)
                ->get();

            if ($offlinePenjualan->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data penjualan offline yang perlu di-push'
                ]);
            }

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($offlinePenjualan as $penjualan) {
                try {
                    // Kirim ke server
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Accept' => 'application/json',
                    ])
                        ->timeout(30)
                        ->post($this->serverUrl . '/api/penjualan/receive', [
                            'offline_id' => $penjualan->id,
                            'no_faktur' => $penjualan->no_faktur,
                            'tanggal_penjualan' => $penjualan->tanggal_penjualan->format('Y-m-d'),
                            'pasien_id' => $penjualan->pasien_id,
                            'jenis' => $penjualan->jenis,
                            'keterangan' => $penjualan->keterangan,
                            'subtotal' => $penjualan->subtotal,
                            'diskon_total' => $penjualan->diskon_total,
                            'ppn_total' => $penjualan->ppn_total,
                            'tuslah_total' => $penjualan->tuslah_total,
                            'embalase_total' => $penjualan->embalase_total,
                            'grand_total' => $penjualan->grand_total,
                            'bayar' => $penjualan->bayar,
                            'kembalian' => $penjualan->kembalian,
                            'detail' => $penjualan->details->map(function ($detail) {
                                return [
                                    'obat_id' => $detail->obat_id,
                                    'satuan_id' => $detail->satuan_id,
                                    'jumlah' => $detail->jumlah,
                                    'harga_beli' => $detail->harga_beli,
                                    'harga' => $detail->harga,
                                    'subtotal' => $detail->subtotal,
                                    'diskon' => $detail->diskon,
                                    'ppn' => $detail->ppn,
                                    'tuslah' => $detail->tuslah,
                                    'embalase' => $detail->embalase,
                                    'total' => $detail->total,
                                    'no_batch' => $detail->no_batch,
                                    'lokasi_id' => $detail->lokasi_id,
                                ];
                            })->toArray()
                        ]);

                    if ($response->successful()) {
                        $result = $response->json();

                        // Update penjualan lokal: is_online = true
                        $penjualan->update([
                            'is_online' => true,
                            'server_id' => $result['data']['id'] ?? null,
                        ]);

                        $successCount++;

                        Log::info('Penjualan pushed successfully', [
                            'local_id' => $penjualan->id,
                            'server_id' => $result['data']['id'] ?? null,
                            'no_faktur' => $penjualan->no_faktur
                        ]);
                    } else {
                        throw new \Exception($response->body());
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'no_faktur' => $penjualan->no_faktur,
                        'error' => $e->getMessage()
                    ];

                    Log::error('Failed to push penjualan', [
                        'local_id' => $penjualan->id,
                        'no_faktur' => $penjualan->no_faktur,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil push {$successCount} penjualan, gagal {$failedCount}",
                'data' => [
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Push error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PULL penjualan online dari server
     */
    public function pull()
    {
        try {
            // Request data penjualan dari server yang is_online = true
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
            ])
                ->timeout(60)
                ->get($this->serverUrl . '/api/penjualan/online');

            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil data dari server: ' . $response->body());
            }

            $serverPenjualan = $response->json()['data'] ?? [];

            if (empty($serverPenjualan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data penjualan online dari server'
                ]);
            }

            DB::beginTransaction();

            $successCount = 0;
            $skippedCount = 0;

            foreach ($serverPenjualan as $data) {
                // Skip jika sudah ada (cek berdasarkan server_id)
                $exists = Penjualan::where('server_id', $data['id'])->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Insert penjualan
                $penjualan = Penjualan::create([
                    'no_faktur' => $data['no_faktur'],
                    'tanggal_penjualan' => $data['tanggal_penjualan'],
                    'pasien_id' => $data['pasien_id'],
                    'jenis' => $data['jenis'],
                    'keterangan' => $data['keterangan'],
                    'subtotal' => $data['subtotal'],
                    'diskon_total' => $data['diskon_total'],
                    'ppn_total' => $data['ppn_total'],
                    'tuslah_total' => $data['tuslah_total'],
                    'embalase_total' => $data['embalase_total'],
                    'grand_total' => $data['grand_total'],
                    'bayar' => $data['bayar'],
                    'kembalian' => $data['kembalian'],
                    'user_id' => auth()->id(),
                    'is_online' => true, // Data dari server = online
                    'server_id' => $data['id'], // ID dari server
                ]);

                // Insert details
                foreach ($data['details'] as $detail) {
                    PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'obat_id' => $detail['obat_id'],
                        'satuan_id' => $detail['satuan_id'],
                        'jumlah' => $detail['jumlah'],
                        'harga_beli' => $detail['harga_beli'],
                        'harga' => $detail['harga'],
                        'subtotal' => $detail['subtotal'],
                        'diskon' => $detail['diskon'],
                        'ppn' => $detail['ppn'],
                        'tuslah' => $detail['tuslah'],
                        'embalase' => $detail['embalase'],
                        'total' => $detail['total'],
                        'no_batch' => $detail['no_batch'],
                        'lokasi_id' => $detail['lokasi_id'],
                    ]);
                }

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil pull {$successCount} penjualan, {$skippedCount} duplikat di-skip",
                'data' => [
                    'success' => $successCount,
                    'skipped' => $skippedCount
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pull error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check connection ke server
     */
    public function checkConnection()
    {
        try {
            $response = Http::timeout(5)->get($this->serverUrl . '/api/ping');

            return response()->json([
                'is_online' => $response->successful()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'is_online' => false
            ]);
        }
    }
}
