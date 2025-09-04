<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === ROLES ===
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Superadmin', 'description' => 'Full access to all system features'],
            ['id' => 2, 'name' => 'Apoteker', 'description' => 'Access to manage medications and prescriptions'],
        ]);

        // === USERS ===
        DB::table('users')->insert([
            [
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'is_active' => 1,
            ],
            [
                'name' => 'Apoteker Demo',
                'email' => 'apoteker@example.com',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'is_active' => 1,
            ],
        ]);

        // === GOLONGAN OBAT ===
        DB::table('golongan_obat')->insert([
            ['nama' => 'Antibiotik', 'keterangan' => 'Obat untuk melawan infeksi bakteri'],
            ['nama' => 'Analgesik', 'keterangan' => 'Pereda nyeri'],
            ['nama' => 'Antipiretik', 'keterangan' => 'Penurun panas'],
            ['nama' => 'Antidepresan', 'keterangan' => 'Mengatasi depresi'],
            ['nama' => 'Antihipertensi', 'keterangan' => 'Menurunkan tekanan darah'],
            ['nama' => 'Antihistamin', 'keterangan' => 'Mengurangi alergi'],
            ['nama' => 'Antijamur', 'keterangan' => 'Melawan infeksi jamur'],
            ['nama' => 'Vitamin', 'keterangan' => 'Suplemen kesehatan'],
            ['nama' => 'Obat Herbal', 'keterangan' => 'Berbasis tanaman'],
            ['nama' => 'Kortikosteroid', 'keterangan' => 'Mengurangi peradangan'],
        ]);

        // === KATEGORI OBAT ===
        DB::table('kategori_obat')->insert([
            ['nama' => 'Obat Bebas', 'keterangan' => 'Dapat dibeli tanpa resep'],
            ['nama' => 'Obat Bebas Terbatas', 'keterangan' => 'Butuh pengawasan apoteker'],
            ['nama' => 'Obat Keras', 'keterangan' => 'Harus dengan resep dokter'],
            ['nama' => 'Narkotika', 'keterangan' => 'Sangat ketat penggunaannya'],
            ['nama' => 'Psikotropika', 'keterangan' => 'Mengendalikan fungsi psikis'],
            ['nama' => 'Obat Generik', 'keterangan' => 'Obat dengan nama zat aktif'],
            ['nama' => 'Obat Paten', 'keterangan' => 'Obat dengan nama dagang'],
            ['nama' => 'Fitofarmaka', 'keterangan' => 'Obat herbal yang terstandar'],
            ['nama' => 'Suplemen', 'keterangan' => 'Penunjang kesehatan'],
            ['nama' => 'Vaksin', 'keterangan' => 'Pencegah penyakit'],
        ]);

        // === LOKASI OBAT ===
        DB::table('lokasi_obat')->insert([
            ['nama' => 'Rak Depan'],
            ['nama' => 'Rak Tengah'],
            ['nama' => 'Rak Belakang'],
            ['nama' => 'Gudang Pendingin'],
            ['nama' => 'Gudang Biasa'],
            ['nama' => 'Rak Anak'],
            ['nama' => 'Rak Vitamin'],
            ['nama' => 'Rak Herbal'],
            ['nama' => 'Rak Resep'],
            ['nama' => 'Rak Obat Keras'],
        ]);

        // === SATUAN OBAT ===
        DB::table('satuan_obat')->insert([
            ['nama' => 'pcs'],
            ['nama' => 'tablet'],
            ['nama' => 'kapsul'],
            ['nama' => 'botol'],
            ['nama' => 'tube'],
            ['nama' => 'ampul'],
            ['nama' => 'vial'],
            ['nama' => 'sachet'],
            ['nama' => 'box'],
            ['nama' => 'strip'],
        ]);

        // === SUPPLIERS ===
        DB::table('suppliers')->insert([
            ['kode' => 'SUP2509020001', 'nama' => 'PT ABC', 'alamat' => 'Bandung, Jawa Barat', 'kota' => 'Bandung', 'telepone' => '082240356763'],
            ['kode' => 'SUP2509020002', 'nama' => 'PT XYZ', 'alamat' => 'Jakarta', 'kota' => 'Jakarta', 'telepone' => '081234567890'],
            ['kode' => 'SUP2509020003', 'nama' => 'CV Sehat', 'alamat' => 'Surabaya', 'kota' => 'Surabaya', 'telepone' => '081298765432'],
            ['kode' => 'SUP2509020004', 'nama' => 'PT Farma', 'alamat' => 'Yogyakarta', 'kota' => 'Yogyakarta', 'telepone' => '081223344556'],
            ['kode' => 'SUP2509020005', 'nama' => 'CV Obatku', 'alamat' => 'Semarang', 'kota' => 'Semarang', 'telepone' => '081332211445'],
            ['kode' => 'SUP2509020006', 'nama' => 'PT Medika', 'alamat' => 'Medan', 'kota' => 'Medan', 'telepone' => '082167894321'],
            ['kode' => 'SUP2509020007', 'nama' => 'PT Prima', 'alamat' => 'Makassar', 'kota' => 'Makassar', 'telepone' => '085312345678'],
            ['kode' => 'SUP2509020008', 'nama' => 'CV Herbalindo', 'alamat' => 'Denpasar', 'kota' => 'Denpasar', 'telepone' => '085623456789'],
            ['kode' => 'SUP2509020009', 'nama' => 'PT Nusantara Sehat', 'alamat' => 'Palembang', 'kota' => 'Palembang', 'telepone' => '081245678912'],
            ['kode' => 'SUP2509020010', 'nama' => 'PT Kesehatan Jaya', 'alamat' => 'Malang', 'kota' => 'Malang', 'telepone' => '082134567890'],
        ]);

        // === PABRIK ===
        DB::table('pabrik')->insert([
            ['kode' => 'PB001', 'nama' => 'Pabrik A', 'alamat' => 'Jakarta'],
            ['kode' => 'PB002', 'nama' => 'Pabrik B', 'alamat' => 'Bandung'],
            ['kode' => 'PB003', 'nama' => 'Pabrik C', 'alamat' => 'Surabaya'],
            ['kode' => 'PB004', 'nama' => 'Pabrik D', 'alamat' => 'Medan'],
            ['kode' => 'PB005', 'nama' => 'Pabrik E', 'alamat' => 'Yogyakarta'],
            ['kode' => 'PB006', 'nama' => 'Pabrik F', 'alamat' => 'Semarang'],
            ['kode' => 'PB007', 'nama' => 'Pabrik G', 'alamat' => 'Makassar'],
            ['kode' => 'PB008', 'nama' => 'Pabrik H', 'alamat' => 'Denpasar'],
            ['kode' => 'PB009', 'nama' => 'Pabrik I', 'alamat' => 'Palembang'],
            ['kode' => 'PB010', 'nama' => 'Pabrik J', 'alamat' => 'Malang'],
        ]);

        // === PASIEN ===
        DB::table('pasien')->insert([
            ['code' => 'PSN001', 'nama' => 'Andi Setiawan', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1990-01-01', 'alamat' => 'Bandung'],
            ['code' => 'PSN002', 'nama' => 'Budi Santoso', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1985-02-02', 'alamat' => 'Jakarta'],
            ['code' => 'PSN003', 'nama' => 'Citra Dewi', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1992-03-03', 'alamat' => 'Surabaya'],
            ['code' => 'PSN004', 'nama' => 'Dewi Lestari', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1988-04-04', 'alamat' => 'Yogyakarta'],
            ['code' => 'PSN005', 'nama' => 'Eko Saputra', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1995-05-05', 'alamat' => 'Medan'],
            ['code' => 'PSN006', 'nama' => 'Fitriani', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1993-06-06', 'alamat' => 'Semarang'],
            ['code' => 'PSN007', 'nama' => 'Gilang Ramadhan', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1991-07-07', 'alamat' => 'Makassar'],
            ['code' => 'PSN008', 'nama' => 'Hana Puspita', 'jenis_kelamin' => 'Perempuan', 'tanggal_lahir' => '1994-08-08', 'alamat' => 'Denpasar'],
            ['code' => 'PSN009', 'nama' => 'Indra Kurniawan', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1996-09-09', 'alamat' => 'Palembang'],
            ['code' => 'PSN010', 'nama' => 'Joko Widodo', 'jenis_kelamin' => 'Laki-laki', 'tanggal_lahir' => '1975-10-10', 'alamat' => 'Solo'],
        ]);

        // === PENGELUARAN ===
        DB::table('pengeluaran')->insert([
            ['nama' => 'Bayar Tukang Listrik', 'tanggal' => '2025-09-03', 'jumlah' => 25000, 'user_id' => 1],
            ['nama' => 'Bayar Air', 'tanggal' => '2025-09-02', 'jumlah' => 100000, 'user_id' => 1],
            ['nama' => 'Bayar Internet', 'tanggal' => '2025-09-01', 'jumlah' => 250000, 'user_id' => 1],
            ['nama' => 'Beli ATK', 'tanggal' => '2025-08-31', 'jumlah' => 50000, 'user_id' => 2],
            ['nama' => 'Service AC', 'tanggal' => '2025-08-30', 'jumlah' => 150000, 'user_id' => 1],
            ['nama' => 'Konsumsi Rapat', 'tanggal' => '2025-08-29', 'jumlah' => 200000, 'user_id' => 2],
            ['nama' => 'Beli Obat Sample', 'tanggal' => '2025-08-28', 'jumlah' => 300000, 'user_id' => 1],
            ['nama' => 'Bayar Cleaning Service', 'tanggal' => '2025-08-27', 'jumlah' => 100000, 'user_id' => 1],
            ['nama' => 'Beli Printer', 'tanggal' => '2025-08-26', 'jumlah' => 1500000, 'user_id' => 2],
            ['nama' => 'Transportasi', 'tanggal' => '2025-08-25', 'jumlah' => 50000, 'user_id' => 1],
        ]);

        // === SETTINGS ===
        DB::table('settings')->insert([
            'nama_apotek' => 'Apotek Dhyandra',
            'logo' => null,
            'alamat' => 'Jl. Contoh No. 123, Bandung',
            'telepon' => '0221234567',
            'email' => 'info@apotekdhyandra.com',
        ]);
    }
}
