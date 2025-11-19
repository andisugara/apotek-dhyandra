# Setup Aplikasi Offline-Online Sync

Dokumentasi lengkap untuk setup sistem sinkronisasi offline-online untuk Apotek Dhyandra.

## Arsitektur Sistem

Sistem ini menggunakan **2 aplikasi terpisah**:

1. **Aplikasi Online (Server/Master)**

    - Database: MySQL di server online
    - Base URL: `https://server-online.com`
    - Fungsi: Menerima data dari kasir offline, menyimpan semua transaksi

2. **Aplikasi Offline (Kasir/Lokal)**
    - Database: MySQL/SQLite di komputer kasir
    - Fungsi: Input penjualan offline, sync ke server saat online

## Setup Aplikasi Online (Server)

### 1. Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### 2. Konfigurasi Sanctum

Edit `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### 3. Generate Sanctum Token

Jalankan command berikut untuk generate token:

```bash
php artisan sync:generate-token
```

**Output:**

```
==================================================
Sanctum Token Generated Successfully!
==================================================

User: Admin (ID: 1)
Token Name: offline-sync

Copy this token and add to your OFFLINE app .env file:

MASTER_API_TOKEN=1|abcdefghijklmnopqrstuvwxyz1234567890

⚠️  This token will only be shown once. Please save it now!
==================================================
```

**Simpan token ini!** Anda akan memerlukannya untuk setup aplikasi offline.

### 4. .env Configuration (Online App)

```env
APP_MODE=online
APP_URL=https://server-online.com
```

### 5. Routes yang Aktif

File `routes/api.php` sudah dikonfigurasi dengan endpoints:

-   `POST /api/penjualan/receive` - Terima data dari offline
-   `GET /api/penjualan/online` - Kirim data ke offline
-   `GET /api/ping` - Health check

## Setup Aplikasi Offline (Kasir)

### 1. Clone atau Copy Aplikasi

Clone repository yang sama atau copy folder aplikasi ke komputer kasir.

### 2. Database Setup

Gunakan MySQL atau SQLite lokal:

**Opsi A: MySQL**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_offline
DB_USERNAME=root
DB_PASSWORD=
```

**Opsi B: SQLite (Recommended untuk offline)**

```env
DB_CONNECTION=sqlite
# Buat file database.sqlite di folder database/
```

### 3. Run Migration

```bash
php artisan migrate
```

### 4. .env Configuration (Offline App)

**Penting!** Tambahkan konfigurasi berikut:

```env
APP_MODE=offline
MASTER_SERVER_URL=https://server-online.com
MASTER_API_TOKEN=1|abcdefghijklmnopqrstuvwxyz1234567890
```

**Penjelasan:**

-   `APP_MODE=offline` - Mengaktifkan mode offline
-   `MASTER_SERVER_URL` - URL aplikasi online/server
-   `MASTER_API_TOKEN` - Token dari step sebelumnya

### 5. Seed Data Master

**PENTING:** Obat, Pasien, User, dan Lokasi harus **sama persis** antara online dan offline!

**Cara sync data master:**

1. Export dari server online:

```bash
php artisan db:seed --class=MasterDataSeeder
```

2. Import ke aplikasi offline:

```bash
# Copy file SQL atau jalankan seeder yang sama
```

## Cara Menggunakan Sync

### Di Aplikasi Offline (Kasir)

1. **Akses Halaman Sync**

    - Buka menu **Sync** di sidebar
    - URL: `http://localhost/sync`

2. **Lihat Status**

    - **Penjualan Offline**: Transaksi yang belum di-upload
    - **Penjualan Online**: Transaksi yang sudah di-sync
    - **Connection Status**: Indikator koneksi ke server

3. **Push Data (Upload ke Server)**

    - Klik tombol **"Push to Server"**
    - Sistem akan upload semua penjualan offline ke server
    - Setelah sukses, status berubah jadi online

4. **Pull Data (Download dari Server)**
    - Klik tombol **"Pull from Server"**
    - Sistem akan download penjualan dari server
    - Data akan tersimpan di database lokal

### Invoice Numbering

**Offline Invoice:**

```
OFF-20251119-0001
OFF-20251119-0002
```

**Online Invoice:**

```
INV-20251119-0001
INV-20251119-0002
```

Format: `{PREFIX}{YYYYMMDD}-{SEQUENCE}`

## Database Schema

### Tabel: penjualans

Kolom baru yang ditambahkan:

```sql
is_online TINYINT(1) DEFAULT 1  -- false = offline, true = online
server_id BIGINT UNSIGNED NULL  -- ID di server (setelah sync)
```

### Eloquent Scopes

```php
// Get offline penjualan
$offline = Penjualan::offline()->get();

// Get online penjualan
$online = Penjualan::online()->get();

// Count
$count = Penjualan::offline()->count();
```

## API Endpoints

### 1. Ping (Health Check)

**Request:**

```http
GET /api/ping
```

**Response:**

```json
{
    "status": "ok",
    "message": "Server is online",
    "timestamp": "2025-11-19 22:45:00"
}
```

### 2. Receive Penjualan

**Request:**

```http
POST /api/penjualan/receive
Authorization: Bearer {token}
Content-Type: application/json

{
  "offline_id": 123,
  "no_faktur": "OFF-20251119-0001",
  "tanggal": "2025-11-19",
  "pasien_id": 1,
  "nama_pasien": "John Doe",
  "dokter": "Dr. Smith",
  "total": 50000,
  "bayar": 50000,
  "kembalian": 0,
  "detail": [
    {
      "obat_id": 1,
      "qty": 2,
      "satuan": "PCS",
      "harga": 25000,
      "diskon_persen": 0,
      "diskon_rp": 0,
      "total": 50000,
      "lokasi_id": 1
    }
  ]
}
```

**Response (Success):**

```json
{
    "success": true,
    "message": "Penjualan berhasil diterima",
    "data": {
        "id": 456,
        "no_faktur": "OFF-20251119-0001",
        "offline_id": 123
    }
}
```

**Response (Duplicate):**

```json
{
    "success": false,
    "message": "Penjualan dengan no_faktur ini sudah ada",
    "data": {
        "id": 456
    }
}
```

### 3. Send Online Penjualan

**Request:**

```http
GET /api/penjualan/online?limit=100
Authorization: Bearer {token}
```

**Optional Parameters:**

-   `from_date` - Filter dari tanggal
-   `to_date` - Filter sampai tanggal
-   `limit` - Jumlah maksimal (default: 100)

**Response:**

```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 456,
      "no_faktur": "INV-20251119-0001",
      "tanggal": "2025-11-19",
      "pasien_id": 1,
      "nama_pasien": "John Doe",
      "dokter": "Dr. Smith",
      "total": 50000,
      "bayar": 50000,
      "kembalian": 0,
      "user_id": 1,
      "detail": [...]
    }
  ]
}
```

## Troubleshooting

### 1. Connection Status: Offline

**Penyebab:**

-   Server tidak bisa diakses
-   URL salah di .env
-   Firewall memblokir

**Solusi:**

```bash
# Test koneksi manual
curl https://server-online.com/api/ping

# Check .env
cat .env | grep MASTER_SERVER_URL
```

### 2. Push Failed: Unauthorized

**Penyebab:**

-   Token salah atau expired
-   Token tidak di-set di .env

**Solusi:**

```bash
# Generate token baru di server
php artisan sync:generate-token

# Update .env offline app
MASTER_API_TOKEN={new_token}
```

### 3. Stock Mismatch

**Penyebab:**

-   Data obat berbeda antara offline dan online
-   Stock tidak sync

**Solusi:**

-   Sync data master terlebih dahulu
-   Pastikan ID obat sama di kedua database

### 4. Duplicate Entry Error

**Penyebab:**

-   no_faktur sudah ada di server
-   Data sudah pernah di-push sebelumnya

**Solusi:**

-   Cek di server apakah data sudah masuk
-   Jika sudah ada, update is_online=true di offline app

## Monitoring & Logs

### Server Logs

```bash
tail -f storage/logs/laravel.log | grep "Penjualan received"
```

**Success Log:**

```
[2025-11-19 22:45:00] local.INFO: Penjualan received from offline app {"offline_id":123,"server_id":456,"no_faktur":"OFF-20251119-0001"}
```

**Error Log:**

```
[2025-11-19 22:45:00] local.ERROR: Error receiving penjualan from offline {"error":"..."}
```

### Offline Logs

```bash
tail -f storage/logs/laravel.log | grep "Sync"
```

## Security Considerations

1. **HTTPS Wajib untuk Production**

    - `MASTER_SERVER_URL` harus menggunakan https://
    - Aktifkan SSL di server online

2. **Token Security**

    - Jangan commit `.env` ke git
    - Regenerate token secara berkala
    - Revoke token jika komputer kasir hilang

3. **Database Backup**
    - Backup database offline setiap hari
    - Backup database online setiap jam

## Best Practices

1. **Push Secara Berkala**

    - Push data minimal 1x sehari saat online
    - Jangan tunggu sampai data menumpuk

2. **Pull untuk Laporan**

    - Pull data untuk melihat transaksi dari kasir lain
    - Pull sebelum generate laporan lengkap

3. **Check Connection**

    - Dashboard sync auto-check setiap 30 detik
    - Manual check sebelum push/pull

4. **Data Master Consistency**
    - Update data master (obat, pasien) di server terlebih dahulu
    - Sync ke offline secara manual atau via export/import

## Support

Jika ada masalah atau pertanyaan, hubungi developer atau check logs untuk error detail.

---

**Version:** 1.0  
**Last Updated:** 19 November 2025
