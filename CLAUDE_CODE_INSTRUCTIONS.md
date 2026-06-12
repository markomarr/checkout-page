# Instruksi Claude Code — Darinari Free Plan Checkout Page
> Baca seluruh dokumen ini sebelum memulai pekerjaan apapun.
> PRD autoritatif ada di: `PRD.md`

---

## Prinsip kerja global (berlaku di semua tahap)

1. **Baca PRD sebelum menulis kode apapun.** Jika ada konflik antara instruksi tahap ini dan PRD, PRD menang.
2. **Jangan over-build.** Bangun hanya yang diminta di tahap ini. Fitur fase berikutnya ada di PRD Section 1.5 — jangan disentuh.
3. **Jangan tebak.** Jika ada ambiguitas yang tidak tercakup PRD dan tidak bisa disimpulkan dari konteks — berhenti dan tanyakan. Jangan lanjut dengan asumsi.
4. **Satu tahap selesai dulu.** Jangan mulai tahap berikutnya sebelum tahap sekarang selesai dan checklist-nya terpenuhi semua.
5. **Verifikasi sebelum lanjut.** Di akhir setiap tahap, jalankan checklist verifikasi. Jika ada item yang gagal, perbaiki dulu sebelum lapor selesai.
6. **Commit kecil dan sering.** Setelah setiap sub-task selesai dan verified, commit dengan pesan yang deskriptif. Jangan kumpulkan banyak perubahan dalam satu commit besar.
7. **Jangan ubah yang sudah verified.** Jika tahap sebelumnya sudah di-checklist, jangan refactor atau ubah strukturnya kecuali ada bug yang ditemukan di tahap berikutnya.

---

## Stack referensi

```
Laravel 11
├── Filament v3           → Brand Owner Panel (/brand) + Super Admin Panel (/admin)
├── Blade + Tailwind CSS  → Public Checkout Page (customer-facing)
├── Alpine.js             → Interaktivitas ringan di checkout (via Filament, tidak perlu install terpisah)
├── SQLite                → Database lokal (file: database/database.sqlite)
└── Laravel Storage       → File upload lokal (storage/app/public, symlink via storage:link)
```

---

## Tahap 1 — Fondasi project

**Tujuan:** Project Laravel berjalan, database siap, struktur folder established.

### Yang dikerjakan

```
1a. Install Laravel 11 baru
1b. Konfigurasi environment (.env) untuk SQLite
1c. Buat semua file migrasi database
1d. Buat semua Model dengan relasi dan fillable
1e. Jalankan migrasi + buat seeder untuk super admin
1f. Setup Laravel storage symlink
```

### Instruksi detail

**1a. Install project:**
```bash
composer create-project laravel/laravel darinari-checkout
cd darinari-checkout
```

**1b. Konfigurasi .env:**
```
DB_CONNECTION=sqlite
# Hapus atau comment baris DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# SQLite akan otomatis buat file database/database.sqlite
```
Buat file database kosong:
```bash
touch database/database.sqlite
```

**1c. Migrasi database — buat file-file ini (urutan sesuai dependensi FK):**

File: `create_users_table`
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['super_admin', 'brand_owner']);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

File: `create_brands_table`
```php
Schema::create('brands', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('logo_path')->nullable();
    $table->string('bank_name', 100)->nullable();
    $table->string('bank_account_number', 50)->nullable();
    $table->string('bank_account_name')->nullable();
    $table->string('qris_image_path')->nullable();
    $table->string('whatsapp_number', 20);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

File: `create_products_table`
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug');
    $table->text('description')->nullable();
    $table->unsignedInteger('price');
    $table->unsignedInteger('stock')->default(0);
    $table->string('image_path')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->unique(['brand_id', 'slug']); // slug unik per brand, BUKAN global
});
```

File: `create_orders_table`
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained();
    $table->foreignId('brand_id')->constrained();
    $table->string('order_code', 50)->unique();
    $table->string('customer_name');
    $table->string('customer_phone', 20);
    $table->text('customer_address');
    $table->enum('courier', ['jne', 'jnt', 'sicepat']);
    $table->enum('payment_method', ['bank_transfer', 'qris']);
    $table->string('payment_proof_path');
    $table->unsignedInteger('quantity');
    $table->unsignedBigInteger('total_price');
    $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'done', 'cancelled'])
          ->default('pending');
    $table->timestamps();
});
```

**1d. Model — buat dengan relasi lengkap:**

`User.php`: relasi `hasOne(Brand::class)`, scope untuk filter role, method `isSuperAdmin()`, `isBrandOwner()`

`Brand.php`: relasi `belongsTo(User::class)`, `hasMany(Product::class)`, `hasMany(Order::class)`

`Product.php`: relasi `belongsTo(Brand::class)`, `hasMany(Order::class)`, scope `active()`

`Order.php`: relasi `belongsTo(Product::class)`, `belongsTo(Brand::class)`

Semua model harus punya `$fillable` yang lengkap. Jangan pakai `$guarded = []`.

**1e. Seeder:**

Buat `DatabaseSeeder` yang memanggil `SuperAdminSeeder`.

`SuperAdminSeeder` membuat satu user super admin:
```
name: Super Admin
email: admin@darinari.co.id
password: password (di-hash dengan Hash::make)
role: super_admin
is_active: true
```

**1f. Storage symlink:**
```bash
php artisan storage:link
```

### Checklist verifikasi tahap 1

Sebelum lanjut ke tahap 2, semua item ini harus terpenuhi:

- [ ] `php artisan migrate --seed` berjalan tanpa error
- [ ] File `database/database.sqlite` ada dan tidak kosong (ada tabel)
- [ ] `php artisan tinker` → `User::count()` → return `1`
- [ ] `php artisan tinker` → `User::first()->role` → return `"super_admin"`
- [ ] `php artisan storage:link` berhasil, folder `public/storage` ada
- [ ] Semua 4 tabel ada di DB (users, brands, products, orders)
- [ ] `php artisan migrate:status` semua migration status = `Ran`

---

## Tahap 2 — Filament: setup dua panel + Super Admin resources

**Tujuan:** Super admin bisa login dan mengelola brand owner dari dashboard.

### Yang dikerjakan

```
2a. Install Filament v3
2b. Buat AdminPanelProvider (/admin) untuk super admin
2c. Buat BrandPanelProvider (/brand) untuk brand owner (struktur saja, belum diisi resource)
2d. Buat UserResource di Admin panel (kelola brand owner)
2e. Buat OrderResource di Admin panel (monitor semua order — read only)
2f. Guard dan middleware per panel
```

### Instruksi detail

**2a. Install Filament:**
```bash
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

**2b. AdminPanelProvider:**
- Path: `/admin`
- Guard: hanya user dengan `role = 'super_admin'` dan `is_active = true`
- Login page: default Filament
- Tambahkan middleware check role — jika bukan super_admin, redirect ke `/brand/login`

**2c. BrandPanelProvider:**
- Path: `/brand`
- Guard: hanya user dengan `role = 'brand_owner'` dan `is_active = true`
- Jika `is_active = false`, jangan biarkan login — redirect dengan pesan "Akun tidak aktif"
- Kosong dulu — resource akan diisi di tahap 3

**2d. UserResource di Admin panel:**

Ini adalah resource untuk mengelola brand owner. Kolom tabel yang ditampilkan:
- Nama, email, status aktif (badge), nama brand, jumlah produk, jumlah order, tanggal dibuat

Form create/edit:
- Nama (text, required)
- Email (email, required, unique)
- Password (password, required saat create, opsional saat edit — jika kosong tidak diubah)
- Nama brand (text, required) → disimpan ke tabel `brands.name`
- Slug brand (text, required, auto-generate dari nama brand, editable) → `brands.slug`
- Nomor WhatsApp brand (text, required) → `brands.whatsapp_number`

Logic saat create:
1. Buat record `users` dengan role `brand_owner`
2. Otomatis buat record `brands` yang terasosiasi dengan user baru tersebut

Actions yang tersedia:
- Toggle aktif/nonaktif (action di tabel) — ubah `users.is_active` DAN `brands.is_active` secara bersamaan
- Edit
- Hapus — hanya diizinkan jika brand owner tidak punya order. Jika punya order, tampilkan error "Brand owner ini memiliki order aktif dan tidak bisa dihapus. Nonaktifkan saja."

**2e. OrderResource di Admin panel:**

Tabel: order_code, nama brand, nama customer, produk, total, status (badge berwarna), tanggal

Filter: by brand, by status, by tanggal (date range)

Actions: view detail (read only) termasuk preview bukti pembayaran

Super admin TIDAK bisa ubah status order dari panel ini.

**2f. Auth middleware:**

Buat middleware atau gunakan Filament `canAccess()` untuk memastikan:
- `/admin/*` hanya bisa diakses `role = super_admin`
- `/brand/*` hanya bisa diakses `role = brand_owner` dengan `is_active = true`
- Akses silang → redirect ke panel login yang sesuai

### Checklist verifikasi tahap 2

- [ ] `php artisan serve` berjalan tanpa error
- [ ] Login ke `/admin/login` dengan `admin@darinari.co.id / password` berhasil
- [ ] Dashboard admin tampil tanpa error
- [ ] Bisa membuat brand owner baru dari admin panel
- [ ] Setelah create brand owner, record `brands` otomatis terbuat (cek via tinker: `Brand::count()`)
- [ ] Toggle nonaktif brand owner berhasil mengubah `is_active` di DB
- [ ] Akses `/brand/login` redirect ke halaman login brand (belum ada resource, tapi panel harus bisa diakses)
- [ ] Akses `/admin` tanpa login → redirect ke `/admin/login`
- [ ] Login ke `/brand` dengan akun super admin → ditolak/redirect

---

## Tahap 3 — Filament: Brand Owner resources

**Tujuan:** Brand owner bisa login dan mengelola produk, order, setting brand, dan password.

### Yang dikerjakan

```
3a. ProductResource di Brand panel
3b. OrderResource di Brand panel
3c. Setting brand (custom Filament page)
3d. Link checkout (custom Filament page)
3e. Ganti password (Filament profile / custom page)
3f. Isolasi data — brand owner hanya lihat data miliknya
```

### Instruksi detail

**3a. ProductResource (Brand panel):**

Tabel: gambar (thumbnail), nama, slug, harga (format Rupiah), stok, status aktif (badge), tanggal dibuat

Form:
- Nama produk (text, required)
- Slug (text, auto-generate dari nama via `Str::slug()`, editable, unique per brand)
- Deskripsi (textarea, optional)
- Harga (integer, required, min 1, tampilkan dengan format Rupiah di preview)
- Stok (integer, required, min 0)
- Gambar produk (file upload, JPG/PNG, maks 2MB, optional, simpan ke `products/{brand_id}/`)
- Status aktif (toggle)

Validasi slug: cek `UNIQUE(brand_id, slug)` — error jika duplikat dalam brand yang sama.

**3b. OrderResource (Brand panel):**

Tabel: order_code, nama customer, produk, jumlah, total (Rupiah), kurir, status (badge berwarna sesuai status), tanggal

Badge status:
- `pending` → warning (kuning)
- `confirmed` → info (biru)
- `processing` → purple
- `shipped` → teal
- `done` → success (hijau)
- `cancelled` → danger (merah)

Filter: by status

View detail order:
- Semua data order
- Preview bukti pembayaran (image preview atau link download untuk PDF)
- Tombol action untuk update status

Actions per status:
- `pending` → tombol "Konfirmasi pembayaran" → ubah ke `confirmed`
- `confirmed` → tombol "Proses pesanan" → ubah ke `processing`
- `processing` → tombol "Tandai dikirim" → ubah ke `shipped`
- `shipped` → tombol "Selesai" → ubah ke `done`
- Semua status kecuali `done` → tombol "Batalkan" → ubah ke `cancelled`
- Saat status diubah ke `confirmed`: tampilkan notifikasi Filament yang berisi link wa.me ke customer

Format link wa.me ke customer saat konfirmasi:
```
https://wa.me/{customer_phone_formatted}?text={pesan_url_encoded}
```
Pesan: "Halo {customer_name}, pesanan Anda dengan kode {order_code} sudah kami konfirmasi dan sedang diproses. Terima kasih!"

**3c. Setting brand (custom Filament page):**

Form yang mengedit record `brands` milik brand owner yang sedang login:
- Logo brand (file upload, JPG/PNG, maks 2MB, simpan ke `logos/{brand_id}/`)
- Nomor WhatsApp (text, required, auto-strip `+` dan format ke internasional)
- Nama bank (text, optional)
- Nomor rekening (text, optional)
- Nama pemilik rekening (text, optional)
- Gambar QRIS (file upload, JPG/PNG, maks 2MB, simpan ke `qris/{brand_id}/`)

Tambahkan warning jika rekening bank DAN QRIS keduanya kosong: "Checkout page belum bisa aktif. Isi minimal satu metode pembayaran."

Brand owner TIDAK bisa mengubah nama brand dan slug dari halaman ini.

**3d. Link checkout (custom Filament page):**

Tampilkan daftar produk aktif milik brand owner dengan URL checkout masing-masing.
Format URL: `http://localhost:8000/{brand_slug}/{product_slug}`

Setiap item punya tombol "Salin link" yang menggunakan Alpine.js clipboard copy.

Empty state jika tidak ada produk aktif: "Belum ada produk aktif. Tambahkan produk di menu Produk."

**3e. Ganti password:**

Gunakan Filament built-in profile page atau buat custom page dengan form:
- Password saat ini (required, diverifikasi dengan `Hash::check`)
- Password baru (required, min 8 karakter)
- Konfirmasi password baru (required, same as password baru)

**3f. Isolasi data:**

Gunakan Filament `Policy` untuk semua resource di Brand panel:
- `ProductPolicy`: brand owner hanya bisa CRUD produk milik brand-nya sendiri
- `OrderPolicy`: brand owner hanya bisa view/update order yang `brand_id`-nya sama dengan brand mereka

Cara enforce: di setiap resource, override `getEloquentQuery()` untuk filter by `brand_id` milik user yang login.

Contoh di ProductResource:
```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->where('brand_id', auth()->user()->brand->id);
}
```

### Checklist verifikasi tahap 3

- [ ] Buat brand owner dari admin panel, login ke `/brand` berhasil
- [ ] Bisa tambah produk baru, produk tersimpan dengan `brand_id` yang benar
- [ ] Slug duplikat dalam brand yang sama → error validasi
- [ ] Upload gambar produk → file tersimpan di `storage/app/public/products/{brand_id}/`
- [ ] Daftar order hanya menampilkan order milik brand yang login (verifikasi dengan 2 brand berbeda)
- [ ] Update status order dari `pending` → `confirmed` berhasil
- [ ] Saat konfirmasi order, link wa.me ke customer muncul dengan format yang benar
- [ ] Setting brand: upload logo tersimpan dan bisa diakses via URL `/storage/logos/...`
- [ ] Halaman link checkout menampilkan URL yang benar untuk setiap produk aktif
- [ ] Tombol salin link berfungsi (cek di browser)
- [ ] Ganti password: password lama salah → error; password baru berhasil diubah
- [ ] Brand owner A tidak bisa akses data brand owner B (coba akses via URL langsung)

---

## Tahap 4 — Public Checkout Page

**Tujuan:** Customer bisa membuka link produk, mengisi form order, dan submit. Ini adalah halaman yang paling penting — mobile-first.

### Yang dikerjakan

```
4a. Route dan controller untuk checkout page
4b. Blade view checkout page (mobile-first)
4c. Logic submit order (Form Request + Service + DB transaction)
4d. Halaman konfirmasi sukses
4e. Handling edge case (stok habis, brand/produk tidak aktif, dll.)
```

### Instruksi detail

**4a. Route:**

Tambahkan di `routes/web.php` SETELAH semua route Filament:
```php
Route::get('/{brand:slug}/{product:slug}', [CheckoutController::class, 'show'])
    ->name('checkout.show');

Route::post('/{brand:slug}/{product:slug}', [CheckoutController::class, 'submit'])
    ->name('checkout.submit');

Route::get('/{brand:slug}/{product:slug}/sukses/{order:order_code}', [CheckoutController::class, 'success'])
    ->name('checkout.success');
```

Gunakan route model binding dengan constraint: `brand` harus `is_active = true`, `product` harus `is_active = true` dan `brand_id` sesuai dengan brand.

**4b. CheckoutController:**

Method `show`:
- Resolve brand via slug, cek `is_active` — jika false → abort(404)
- Resolve product via slug + brand_id, cek `is_active` dan stock — jika tidak ditemukan → abort(404)
- Cek minimal satu metode pembayaran tersedia — jika tidak → return view dengan flag `$paymentNotReady = true`
- Pass `$brand`, `$product`, `$paymentNotReady` ke view

Method `submit`:
- Validasi via `StoreOrderRequest` (Form Request)
- Wrap dalam `DB::transaction()`:
  1. Lock product row: `Product::lockForUpdate()->find($product->id)`
  2. Cek stok mencukupi — jika tidak → rollback, return error
  3. Upload file bukti bayar ke `storage/app/public/payments/{brand_id}/`
  4. Generate `order_code` via `OrderService::generateCode()`
  5. Hitung ulang `total_price` di backend (price × quantity) — jangan percaya input form
  6. Simpan order
  7. Kurangi stok produk
- Redirect ke `checkout.success` dengan `order_code`

Method `success`:
- Resolve order via `order_code`
- Generate link wa.me ke brand owner dengan pesan pre-filled
- Return view konfirmasi

**Format order_code** (`OrderService::generateCode()`):
```php
do {
    $code = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
} while (Order::where('order_code', $code)->exists());
return $code;
```
Maksimal retry 3× — jika masih collision setelah 3× throw exception (sangat jarang terjadi, tapi harus di-handle).

**StoreOrderRequest — rules:**
```php
'customer_name'    => 'required|string|max:255',
'customer_phone'   => 'required|string|regex:/^(\+62|62|0)[0-9]{8,13}$/',
'customer_address' => 'required|string|min:10',
'courier'          => 'required|in:jne,jnt,sicepat',
'quantity'         => 'required|integer|min:1',
'payment_method'   => 'required|in:bank_transfer,qris',
'payment_proof'    => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
```

**4c. Blade view — checkout page (mobile-first):**

Layout requirements:
- `<meta name="viewport" content="width=device-width, initial-scale=1">` — WAJIB
- Font body minimum 16px — agar tidak ada auto-zoom di iOS saat focus input
- Single column, max-width 480px, centered
- Background: `#F8F9FA`, card surface: `#FFFFFF`

Struktur halaman (urutan dari atas ke bawah):
1. Header brand: logo + nama brand (prominent)
2. Card produk: gambar produk, nama, harga per unit, stok tersisa
3. Form section "Data kamu":
   - Input: nama lengkap, nomor WhatsApp, alamat
4. Form section "Pengiriman":
   - Select: pilih kurir (JNE Reguler / J&T Express / SiCepat BEST)
   - Input number: jumlah (dengan tombol +/−)
5. Ringkasan harga (update real-time via Alpine.js saat quantity berubah)
6. Form section "Pembayaran":
   - Toggle/radio: Transfer Bank / QRIS
   - Konten dinamis (Alpine.js x-show):
     - Jika Transfer Bank: tampilkan nama bank, nomor rekening, nama pemilik
     - Jika QRIS: tampilkan gambar QR (min 200×200px)
7. Upload bukti pembayaran
8. Tombol submit "Kirim pesanan"

Warna yang digunakan (CSS variables atau hardcode):
```css
--primary: #1D9E75;
--bg: #F8F9FA;
--surface: #FFFFFF;
--text: #1C1C1E;
--text-secondary: #6B7280;
--border: #E5E7EB;
--error: #EF4444;
```

Tap target: semua elemen interaktif min 44px height.

Input styling: font-size 16px minimum (KRITIS — iOS Safari auto-zoom jika < 16px).

Error display: inline di bawah field, warna `#EF4444`, font-size 14px.

Alpine.js untuk:
- Hitung total real-time: `total = price * quantity`
- Toggle tampilan info pembayaran: `x-show="paymentMethod === 'bank_transfer'"` dll.
- Tombol +/− quantity (min 1, max = stok tersedia)

**4d. Halaman konfirmasi sukses:**

Tampilkan:
- Ikon centang / ilustrasi sukses
- "Pesanan berhasil dikirim!"
- Kode order (prominent, mudah di-screenshot)
- Ringkasan: produk, jumlah, total, metode bayar, kurir
- Tombol besar "Konfirmasi via WhatsApp" → link wa.me ke brand owner dengan pesan pre-filled

Format pesan wa.me:
```
Halo, saya ingin konfirmasi pesanan:

Kode: {order_code}
Produk: {product_name}
Jumlah: {quantity}
Total: Rp {total_price}
Bayar via: {payment_method_label}
Kurir: {courier_label}

Nama: {customer_name}
Alamat: {customer_address}

Bukti transfer sudah saya upload. Mohon dikonfirmasi. Terima kasih!
```

**4e. Edge cases:**

- Stok 0 saat halaman dibuka: tampilkan card produk dengan overlay "Produk sedang habis", semua input disabled
- Brand belum setup pembayaran: tampilkan pesan "Halaman belum siap, silakan hubungi penjual" — bukan 404
- Brand/produk tidak ditemukan atau tidak aktif: abort(404) → Laravel default 404 page (boleh custom nanti)

### Checklist verifikasi tahap 4

- [ ] Buka `http://localhost:8000/{brand-slug}/{product-slug}` di browser HP (atau DevTools mobile view 375px) — tampil tanpa horizontal scroll
- [ ] Semua input bisa diisi dengan nyaman di layar 375px
- [ ] Font size input ≥ 16px (cek: tidak ada auto-zoom di iOS Safari saat tap input)
- [ ] Tap target semua button/select/radio ≥ 44px (cek di DevTools)
- [ ] Total harga update real-time saat quantity diubah
- [ ] Toggle antara Transfer Bank dan QRIS menampilkan info yang benar tanpa reload
- [ ] Submit form kosong → error muncul inline per field, data tidak tersimpan
- [ ] Submit form lengkap dengan file upload → order tersimpan di DB
- [ ] Stok produk berkurang sesuai quantity setelah submit (cek via tinker)
- [ ] Halaman konfirmasi tampil dengan kode order yang benar
- [ ] Link wa.me di halaman konfirmasi terbuka dengan pesan pre-filled yang lengkap
- [ ] URL produk tidak aktif atau tidak ada → 404
- [ ] Test race condition manual: buka dua tab dengan produk stok = 1, submit keduanya bersamaan — hanya satu yang berhasil

---

## Tahap 5 — Polish & hardening

**Tujuan:** Semua edge case tertangani, UI konsisten, tidak ada celah keamanan yang jelas, siap untuk review KangNano.

### Yang dikerjakan

```
5a. Custom 404 page yang minimal dan bersih
5b. CSRF protection sudah aktif di semua form (Laravel default, verifikasi)
5c. Validasi nomor WA: normalisasi format saat simpan ke DB
5d. File URL security: payment proof tidak bisa diakses langsung via URL publik
5e. Filament dashboard: tambahkan metrik ringkas di home page Brand panel
5f. Review dan perbaikan UI mobile checkout page
5g. Test semua flow end-to-end
```

### Instruksi detail

**5a. Custom 404:** Buat `resources/views/errors/404.blade.php` — sederhana, ada pesan "Halaman tidak ditemukan" dan tidak ada link ke halaman lain (checkout page adalah standalone).

**5b. CSRF:** Pastikan semua form Blade punya `@csrf`. Verifikasi bahwa POST checkout tidak bisa dilakukan tanpa CSRF token.

**5c. Normalisasi nomor WA:**

Buat helper atau gunakan mutator di model `Order` dan `Brand`:
```php
// Input: 081234567890 atau +6281234567890 atau 6281234567890
// Output: 6281234567890 (internasional, tanpa +)
private function normalizeWhatsApp(string $number): string
{
    $number = preg_replace('/[^0-9]/', '', $number);
    if (str_starts_with($number, '0')) {
        $number = '62' . substr($number, 1);
    }
    return $number;
}
```

**5d. File security untuk bukti pembayaran:**

File payment proof TIDAK boleh diakses via URL publik langsung (`/storage/payments/...`). Simpan di luar `public` storage atau gunakan route yang memerlukan autentikasi.

Solusi yang digunakan: simpan file payment proof ke `storage/app/private/payments/{brand_id}/` (bukan `public`), dan buat route `GET /brand/orders/{order}/proof` yang hanya bisa diakses brand owner yang punya order tersebut.

**5e. Brand panel home page:**

Override Filament default dashboard dengan widget sederhana:
- Total order bulan ini (milik brand yang login)
- Order pending (perlu tindakan)
- Total pendapatan bulan ini (sum of `total_price` order dengan status bukan `cancelled`)

**5f. Review UI mobile:**

Buka checkout page di DevTools dengan viewport 375px, 390px (iPhone 14), dan 412px (Android standar). Cek:
- Tidak ada elemen yang keluar dari viewport
- Gambar QRIS proporsional dan bisa di-scan
- Tombol submit mudah dijangkau (tidak terlalu ke atas)
- Semua teks terbaca (kontras cukup)

**5g. Test end-to-end:**

Jalankan skenario berikut secara manual dan catat hasilnya:

Skenario 1: Happy path
1. Super admin buat brand owner baru
2. Brand owner login, setup brand (logo, rekening, nomor WA)
3. Brand owner tambah produk (stok: 5)
4. Buka link checkout produk di mobile viewport
5. Isi form, upload bukti bayar, submit
6. Verifikasi stok berkurang jadi 4
7. Brand owner konfirmasi order dari dashboard
8. Update status sampai `done`

Skenario 2: Stok habis
1. Set stok produk ke 0 dari dashboard
2. Buka checkout page → verifikasi form disabled dengan pesan "Produk sedang habis"

Skenario 3: Isolasi data
1. Buat 2 brand owner (Brand A dan Brand B)
2. Login sebagai Brand A, coba akses URL order milik Brand B
3. Verifikasi → 403 atau redirect

Skenario 4: Brand nonaktif
1. Super admin nonaktifkan brand owner
2. Coba login ke `/brand` → verifikasi ditolak
3. Buka checkout page brand tersebut → verifikasi 404

### Checklist verifikasi tahap 5

- [ ] Semua skenario test end-to-end di 5g berjalan sesuai ekspektasi
- [ ] File payment proof tidak bisa diakses langsung via URL `/storage/payments/...`
- [ ] Checkout page di viewport 375px tidak ada horizontal scroll
- [ ] CSRF token ada di semua form (cek source HTML)
- [ ] Nomor WA tersimpan dalam format `62xxx` di DB (tanpa `+`, tanpa `0` di depan)
- [ ] `php artisan serve` berjalan tanpa warning atau error di console

---

## Tahap 6 — Review & handoff ke KangNano

**Tujuan:** Project siap dipresentasikan. Dokumentasi cukup untuk KangNano memahami struktur.

### Yang dikerjakan

```
6a. README.md dengan instruksi setup lokal
6b. Pastikan .env.example lengkap dan tidak ada secret di repo
6c. Final check: php artisan migrate:fresh --seed berjalan bersih dari awal
6d. Commit final dan push ke repo
```

### Instruksi detail

**6a. README.md** harus berisi:
```
# Darinari Free Plan Checkout Page

## Requirement
- PHP 8.2+
- Composer
- Node.js (untuk Vite asset build jika digunakan)

## Setup lokal
1. Clone repo
2. cp .env.example .env
3. composer install
4. php artisan key:generate
5. touch database/database.sqlite
6. php artisan migrate --seed
7. php artisan storage:link
8. php artisan serve

## Akses
- Super Admin: http://localhost:8000/admin (admin@darinari.co.id / password)
- Brand Owner Dashboard: http://localhost:8000/brand
- Checkout Page: http://localhost:8000/{brand-slug}/{product-slug}

## Stack
Laravel 11 · Filament v3 · SQLite · Tailwind CSS · Alpine.js
```

**6b. .env.example:** Pastikan berisi semua key yang dibutuhkan dengan value placeholder. Jangan ada secret asli di `.env.example` atau di repo.

**6c. Fresh install test:**
```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```
Buka browser, pastikan semua halaman bisa diakses.

**6d. .gitignore:** Pastikan `.env`, `database/database.sqlite`, `storage/app/private/`, dan `storage/app/public/` (kecuali `.gitkeep`) tidak ikut di-commit.

### Checklist verifikasi tahap 6 (final)

- [ ] `php artisan migrate:fresh --seed` berjalan bersih tanpa error
- [ ] Super admin bisa login di `/admin`
- [ ] README.md ada dan instruksi setup bisa diikuti dari awal
- [ ] `.env` tidak ada di repo (cek `.gitignore`)
- [ ] `database.sqlite` tidak ada di repo
- [ ] Seluruh 5 checklist tahap sebelumnya sudah terpenuhi

---

## Ringkasan 6 tahap

| Tahap | Fokus | Output utama | Estimasi |
|-------|-------|-------------|----------|
| 1 | Fondasi | Laravel siap, DB ter-migrasi, seeder berjalan | 1–2 jam |
| 2 | Admin panel | Super admin bisa kelola brand owner | 2–3 jam |
| 3 | Brand panel | Brand owner bisa kelola produk, order, setting | 3–4 jam |
| 4 | Checkout page | Customer bisa order via HP | 3–4 jam |
| 5 | Polish | Edge case, security, UI mobile final | 2–3 jam |
| 6 | Handoff | Dokumentasi, clean repo, siap review | 1 jam |

---

## Aturan yang tidak boleh dilanggar

- Jangan install package yang tidak ada di stack referensi tanpa konfirmasi
- Jangan modifikasi tabel migrasi yang sudah di-run — buat migrasi baru jika ada perubahan schema
- Jangan simpan file payment proof di `storage/app/public` — harus di lokasi yang tidak bisa diakses publik
- Jangan skip checklist verifikasi — setiap item ada alasannya
- Jangan lanjut ke tahap berikutnya sebelum semua checklist tahap sekarang terpenuhi
