# Cakrawala - Setup & Run Guide

Panduan ini adalah alur yang dipakai di project Cakrawala ini untuk menjalankan aplikasi dari nol sampai integrasi Midtrans + ngrok aktif.

## 1) Prasyarat

Pastikan sudah terpasang:

- PHP 8.3+
- Composer
- Node.js + npm
- MySQL/MariaDB (XAMPP)
- ngrok

## 2) Masuk ke folder project yang benar

Pastikan berada di folder yang ada file `artisan`.

```powershell
cd C:\xampp\htdocs\cakrawala-new\cakrawala
```

## 3) Install dependency

```powershell
composer install
npm install
```

## 4) Siapkan `.env`

```powershell
copy .env.example .env
php artisan key:generate
```

Isi minimal:

```env
APP_URL=http://127.0.0.1:8080

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cakrawala
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=ISI_SERVER_KEY_SANDBOX
MIDTRANS_CLIENT_KEY=ISI_CLIENT_KEY_SANDBOX
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_VERIFY_SSL=true
MIDTRANS_LOCAL_SIMULATOR=false
MIDTRANS_NOTIFICATION_URL=
```

## 5) Siapkan database

### Opsi A: Import `.sql` dari backup

1. Buka `http://localhost/phpmyadmin`
2. Buat database `cakrawala`
3. Pilih database `cakrawala` -> tab **Import** -> pilih file `.sql` -> **Go**
4. Jalankan:

```powershell
php artisan migrate
```

### Opsi B: Tanpa `.sql` (pakai migration + seed)

```powershell
php artisan migrate:fresh --seed
```

## 6) Final setup Laravel

```powershell
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
```

## 7) Jalankan aplikasi (lokal)

Jalankan proses berikut (bisa terminal terpisah):

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

```powershell
npm run dev
```

```powershell
php artisan queue:listen --tries=1 --timeout=0
```

Atau jalankan sekaligus:

```powershell
composer run dev
```

## 8) PENTING: HTTP lokal vs HTTPS publik

- `php artisan serve` hanya membuka **lokal HTTP** (`http://127.0.0.1:8080`)
- Midtrans webhook butuh URL **publik HTTPS**
- Jadi untuk testing Midtrans, wajib pakai ngrok

## 9) Setup ngrok untuk Midtrans

1. Login token ngrok:

```powershell
ngrok config add-authtoken <TOKEN_NGROK_ANDA>
```

2. Expose app:

```powershell
ngrok http 8080
```

3. Ambil URL HTTPS dari output ngrok (contoh: `https://abcd-1234.ngrok-free.app`)

4. Sinkronkan otomatis ke `.env`:

```powershell
php artisan midtrans:sync-ngrok
```

Command ini otomatis mengisi:

- `APP_URL=https://<ngrok-domain>`
- `MIDTRANS_NOTIFICATION_URL=https://<ngrok-domain>/api/v1/payments/midtrans/notification`

## 10) Set Midtrans Dashboard (Sandbox)

Di Midtrans MAP:

- Gunakan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` yang sama dengan `.env`
- Isi Notification URL:

```text
https://<ngrok-domain>/api/v1/payments/midtrans/notification
```

Endpoint webhook aplikasi ini memang:

```text
POST /api/v1/payments/midtrans/notification
```

## 11) Uji end-to-end

1. Login user/customer
2. Buat booking
3. Pilih metode `midtrans_snap`
4. Selesaikan pembayaran di halaman Snap Midtrans
5. Kembali ke aplikasi, cek status pembayaran
6. Jika status belum berubah, gunakan tombol refresh status di halaman payment

## 12) Akun seed default

- `admin@cakrawala.com`
- `staff@cakrawala.com`
- `manager@cakrawala.com`
- `customer@cakrawala.com`
- Password: `password`

## 13) Troubleshooting singkat

### Login gagal (credentials tidak cocok)

Jalankan:

```powershell
php artisan db:seed
```

### Webhook tidak masuk / status payment tidak update

Periksa:

- ngrok masih aktif?
- domain ngrok berubah?
- sudah jalankan ulang `php artisan midtrans:sync-ngrok`?
- Notification URL di dashboard Midtrans sudah sama persis?

### URL ngrok berubah

Ini normal pada ngrok free saat restart.
Setiap domain berubah, ulangi:

1. `ngrok http 8080`
2. `php artisan midtrans:sync-ngrok`
3. Update Notification URL di Midtrans bila perlu
