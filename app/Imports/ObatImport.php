<?php

namespace App\Imports;

use App\Models\Obat;
use App\Models\ObatSatuan;
use App\Models\Stok;
use App\Models\SatuanObat;
use App\Models\LokasiObat;
use App\Models\GolonganObat;
use App\Models\KategoriObat;
use App\Models\Pabrik;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ObatImport implements ToCollection, WithHeadingRow,  WithBatchInserts, WithChunkReading
{
    protected array $results = [
        'success' => 0,
        'error'   => 0,
        'total'   => 0,
        'errors'  => [],
    ];

    public function collection(Collection $rows)
    {
        $this->results['total'] += $rows->count();
        foreach ($rows as $i => $row) {
            if ($i < 2) continue;
            // normalisasi key→lowercase + trim nilai string
            $row = collect($row)->mapWithKeys(function ($v, $k) {
                return [strtolower($k) => is_string($v) ? trim($v) : $v];
            });

            $rowNumber = $i + 2; // heading di baris 1

            try {
                // --- Wajib: kode_obat & nama_obat ada dan valid ---
                $kodeObat = trim((string)($row[2] ?? ''));
                $namaObat = trim((string)($row[3] ?? ''));
                if ($kodeObat === '' || $kodeObat === '-') {
                    $this->addError($rowNumber, 'Kode obat harus diisi (tidak boleh kosong atau "-").');
                    continue;
                }
                if ($namaObat === '') {
                    $this->addError($rowNumber, 'Nama obat harus diisi.');
                    continue;
                }

                // --- Minimal satu satuan terisi? ---
                $hasAnyUnit = false;
                foreach ([4, 9, 15, 20] as $key) {
                    $v = ($row[$key] ?? '');
                    if ($v !== '' && $v !== '-') {
                        $hasAnyUnit = true;
                        break;
                    }
                }
                if (!$hasAnyUnit) {
                    $this->addError($rowNumber, "Minimal isi salah satu 'Satuan' (Terkecil/2/3/4).");
                    continue;
                }

                DB::beginTransaction();

                // --- FK: golongan & kategori (buat jika belum ada) ---
                $golonganId = null;
                if (!empty($row[24])) {
                    $golonganId = GolonganObat::firstOrCreate(
                        ['nama' => $row[24]],
                        ['keterangan' => '', 'is_active' => true]
                    )->id;
                }

                $kategoriId = null;
                if (!empty($row[25])) {
                    $kategoriId = KategoriObat::firstOrCreate(
                        ['nama' => $row[25]],
                        ['keterangan' => '', 'is_active' => true]
                    )->id;
                }

                // --- Lokasi default (Gudang di Excel di-skip) ---
                $lokasi = LokasiObat::first();
                if (!$lokasi) {
                    $lokasi = LokasiObat::create([
                        'nama'      => 'Gudang Utama',
                        'is_active' => true,
                    ]);
                }

                // --- Upsert OBAT persis pakai kode_obat dari Excel ---
                $obat = Obat::updateOrCreate(
                    ['kode_obat' => $kodeObat],
                    [
                        'nama_obat'    => $namaObat,
                        'pabrik_id'    => 1,       // sesuaikan kebutuhanmu
                        'golongan_id'  => $golonganId,
                        'kategori_id'  => $kategoriId,
                        'jenis_obat'   => '-',
                        'minimal_stok' => 10,
                        'is_active'    => '1',
                    ]
                );

                // --- LEVEL 1 (Satuan Terkecil) ---
                $this->processUnit(
                    $obat,
                    $row,
                    satuanKey: 5,
                    qtyKey: 4,
                    hargaKey: 6,         // Harga 1 utk level 1
                    lokasiId: $lokasi->id
                );

                // --- LEVEL 2 ---
                $this->processUnit($obat, $row, 10, 9, 11, $lokasi->id);

                // --- LEVEL 3 ---
                $this->processUnit($obat, $row, 15, 14, 16, $lokasi->id);

                // --- LEVEL 4 ---
                $this->processUnit($obat, $row, 20, 19, 21, $lokasi->id);

                DB::commit();
                $this->results['success']++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->addError($rowNumber, $e->getMessage());
                Log::error('Error importing obat', [
                    'rowNumber' => $rowNumber,
                    'row'       => $row->toArray(),
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Proses satu level satuan: buat/ambil Satuan, upsert harga di obat_satuan,
     * dan insert stok jika qty > 0. Harga2/3 di-skip; harga_beli = 0.
     */
    protected function processUnit(
        Obat $obat,
        Collection $row,
        string $satuanKey,
        string $qtyKey,
        string $hargaKey,
        int $lokasiId
    ): void {
        $satuanName = ($row[$satuanKey] ?? '') ?: '';
        if ($satuanName === '' || $satuanName === '-') {
            return; // skip kalau kosong / '-'
        }

        $qtyRaw   = $row[$qtyKey]  ?? null;
        $hargaRaw = $row[$hargaKey] ?? null;

        // Satuan
        $satuan = SatuanObat::firstOrCreate(['nama' => $satuanName], ['is_active' => true]);

        // Harga jual = Harga 1
        $hargaJual = $this->parseDecimal($hargaRaw);

        // Upsert obat_satuan
        ObatSatuan::updateOrCreate(
            ['obat_id' => $obat->id, 'satuan_id' => $satuan->id],
            [
                'harga_beli'    => 0.00,
                'diskon_persen' => 0.00,
                'profit_persen' => 10.00,
                'harga_jual'    => $hargaJual,
            ]
        );

        // Stok
        $qty = $this->parseInt($qtyRaw); // "3.00" → 3, "3,00" → 3, "-" → 0
        if ($qty > 0) {
            Stok::create([
                'obat_id'            => $obat->id,
                'satuan_id'          => $satuan->id,
                'lokasi_id'          => $lokasiId,
                'no_batch'           => '-',                // default
                'tanggal_expired'    => now()->addYear(),   // default (+1 thn)
                'qty'                => $qty,
                'qty_awal'           => $qty,
                'harga_beli'         => 0.00,               // sesuai requirement
                'harga_jual'         => $hargaJual ?: null,
                'pembelian_detail_id' => null,
            ]);
        }
    }

    // ----------------- Helpers -----------------

    /** Ubah "26,000.00" / "20.908,00" → 26000.00 / 20908.00  */
    protected function parseDecimal($v): float
    {
        if ($v === null) return 0.0;
        $s = trim((string)$v);
        if ($s === '' || $s === '-') return 0.0;

        $s = preg_replace('/\s+/', '', $s);

        // format ID: 1.234,56
        if (preg_match('/,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
            return (float)$s;
        }

        // format US: 26,000.00
        $s = str_replace(',', '', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
        if ($s === '' || $s === '-' || $s === '.') return 0.0;

        return (float)$s;
    }

    /** Ubah "3.00"/"3,00"/"1.234"/"1,234"/"-" → 3/0/1234/1234/0 */
    protected function parseInt($v): int
    {
        if ($v === null) return 0;
        $s = trim((string)$v);
        if ($s === '' || $s === '-') return 0;

        $s = preg_replace('/\s+/', '', $s);

        // format ID: 1.234,00
        if (preg_match('/,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
            return (int) round((float)$s);
        }

        // format US: 1,234.00 atau 3.00
        $s = str_replace(',', '', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
        if ($s === '' || $s === '-' || $s === '.') return 0;

        return (int) round((float)$s);
    }

    protected function addError(int $row, string $message): void
    {
        $this->results['error']++;
        $this->results['errors'][] = "Baris {$row}: {$message}";
    }

    public function getResults(): array
    {
        return $this->results;
    }

    // -------------- Laravel-Excel configs --------------

    // public function rules(): array
    // {
    //     return [
    //         'kode_obat' => 'required',
    //         'nama_obat' => 'required',
    //         // NOTE: Validasi “minimal satu satuan terisi” dilakukan di logic di atas
    //         // Jika ingin satuan_terkecil wajib, bisa aktifkan:
    //         // 'satuan_terkecil' => 'required',
    //         // 'stok_satuan_terkecil' => 'required|numeric',
    //     ];
    // }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
