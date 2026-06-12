# Darinari Checkout

Aplikasi checkout page multi-brand berbasis Laravel. Setiap brand memiliki halaman checkout produknya sendiri yang dapat diakses publik, sementara pemilik brand dan super admin mengelola data melalui panel admin berbasis Filament.

## Requirements

- PHP 8.2 atau lebih baru
- Composer
- Ekstensi PHP standar untuk Laravel (mbstring, pdo_sqlite, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo)

## Setup Lokal

1. Clone repository:

   ```bash
   git clone <url-repo-ini>
   cd checkout-page
   ```

2. Salin file environment:

   ```bash
   cp .env.example .env
   ```

3. Install dependency PHP:

   ```bash
   composer install
   ```

4. Generate application key:

   ```bash
   php artisan key:generate
   ```

5. Buat file database SQLite:

   ```bash
   touch database/database.sqlite
   ```

6. Jalankan migrasi sekaligus seeder:

   ```bash
   php artisan migrate --seed
   ```

7. Buat symbolic link storage (untuk upload gambar produk/brand):

   ```bash
   php artisan storage:link
   ```

8. Jalankan server lokal:

   ```bash
   php artisan serve
   ```

   Aplikasi dapat diakses di `http://localhost:8000`.

## Akses Aplikasi

| Area | URL |
| --- | --- |
| Panel Super Admin | `/admin` |
| Panel Brand Owner | `/brand` |
| Halaman Checkout (publik) | `/{brand-slug}/{product-slug}` |

### Kredensial Default

Super Admin yang dibuat oleh seeder:

- **Email:** `admin@darinari.co.id`
- **Password:** `password`

## Stack Teknologi

- Laravel 11
- Filament v3
- SQLite
- Tailwind CSS
- Alpine.js
