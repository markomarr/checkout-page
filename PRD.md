# PRD — Darinari Free Plan Checkout Page

> **Versi:** 1.1 · **Tanggal:** 12 Juni 2026 · **Status:** Final
> **Tipe proyek:** APP (auth + DB + multi-role dashboard + public form)
> **Changelog v1.1:** Mobile-first dikonfirmasi sebagai prioritas utama. Semua open questions ditutup.

---

## ATURAN UNTUK AI (baca dulu)

- Semua isi PRD ini **dianggap sudah diputuskan (authoritative)**. Tidak ada lagi item `[KONFIRMASI]`.
- Bila butuh sesuatu yang tidak ada di dokumen ini → **tambahkan ke Section 8 sebagai `[KONFIRMASI]`, jangan menebak.**
- Patuhi **Section 1.5 (out of scope)** dan **Section 7 (konvensi)** secara ketat.
- Section bertanda *(opsional)* diisi hanya jika berlaku untuk tipe proyek ini.

---

## 1. Overview

### 1.1 Masalah

Brand owner baru di ekosistem Darinari — khususnya yang baru memiliki beberapa produk — tidak memiliki saluran penjualan digital sendiri. Membuat website membutuhkan biaya dan waktu yang tidak proporsional untuk skala bisnis mereka saat ini. Akibatnya, proses order masih manual sepenuhnya via chat WhatsApp: tidak ada pencatatan stok otomatis, tidak ada rekap order yang terstruktur, dan brand owner harus mengelola percakapan satu per satu tanpa sistem.

### 1.2 Tujuan

- **Tujuan utama:** Memberikan brand owner di Darinari sebuah halaman checkout yang bisa langsung dipakai tanpa perlu memiliki website, dengan pengelolaan order dan stok yang terpusat.
- **Hasil yang diharapkan:**
  - Brand owner bisa menerima order terstruktur (nama, alamat, ekspedisi, bukti bayar) tanpa mengelola percakapan WhatsApp manual
  - Darinari memiliki data terpusat mengenai brand owner yang menggunakan layanan free plan ini

### 1.3 Pengguna

| Persona | Siapa mereka | Kebutuhan utama |
|---------|--------------|-----------------|
| Customer | Pembeli yang mendapat link checkout dari brand owner via WhatsApp — **mengakses via HP** | Form yang nyaman diisi di layar kecil, proses cepat, konfirmasi jelas |
| Brand Owner | Pemilik brand di ekosistem Darinari, non-teknis — **mengakses via HP maupun desktop** | Melihat order masuk, konfirmasi pembayaran, update status, kelola produk |
| Super Admin | Tim internal Darinari — **mengakses via desktop** | Aktivasi/nonaktifkan brand owner, monitor semua aktivitas |

### 1.4 Success Criteria

- [ ] Customer bisa membuka link `[domain]/[brand-slug]/[product-slug]` di HP, mengisi form order lengkap, upload bukti bayar, dan submit — tanpa login
- [ ] Seluruh elemen checkout page terbaca dan bisa dioperasikan dengan nyaman di layar 375px (iPhone SE) tanpa zoom
- [ ] Brand owner menerima notifikasi WhatsApp segera setelah order masuk
- [ ] Stok produk berkurang otomatis setiap kali order di-submit
- [ ] Brand owner bisa mengubah status order dari dashboard tanpa bantuan tim teknis
- [ ] Super admin bisa mengaktifkan/menonaktifkan brand owner dari dashboard
- [ ] Seluruh alur berjalan di localhost tanpa error untuk review KangNano

### 1.5 Scope

**Dikerjakan (MVP):**
- Public checkout page per produk (customer-facing, tanpa login, mobile-first)
- Upload bukti pembayaran oleh customer
- Brand owner dashboard: kelola produk, kelola order, setting brand
- Super admin dashboard: kelola daftar brand owner, monitor order
- Notifikasi WhatsApp via tombol `wa.me` di halaman konfirmasi (bukan auto-open)
- Pengurangan stok otomatis saat order submit
- Workflow status order: `pending → confirmed → processing → shipped → done / cancelled`
- Auth terpisah untuk brand owner dan super admin (dua Filament panel)
- Brand owner bisa ganti password sendiri dari dashboard
- Super admin bisa melihat bukti pembayaran dari semua order

**TIDAK dikerjakan (eksplisit di luar scope):**
- Payment gateway (tidak ada Midtrans, Xendit, atau sejenisnya)
- COD (cash on delivery)
- Kalkulasi ongkir otomatis (ekspedisi statis tanpa ongkir)
- WhatsApp API / WhatsApp Business API
- SEO (bukan kebutuhan — checkout page adalah direct link)
- Multi-produk dalam satu order (1 order = 1 produk)
- Varian produk (ukuran, warna, dll.)
- Marketplace integration (Shopee, Tokopedia, dll.)
- Email notification
- Native mobile app
- Multi-brand per brand owner (1 akun = 1 brand)
- Warna brand dinamis di checkout page (fase berikutnya)

**Fase berikutnya (jangan dibuat sekarang):**
- Hosting / VPS deployment
- Custom domain per brand
- Warna brand dinamis pada checkout page (CSS variable dari DB)
- Laporan penjualan / export CSV
- Varian produk
- Multi-produk per order
- Integrasi ongkir (RajaOngkir)

---

## 2. Requirements (non-fungsional & constraints)

| Kategori | Requirement |
|----------|-------------|
| Performa | Checkout page harus bisa dirender penuh di koneksi mobile 4G standar Indonesia dalam kondisi wajar. Asset gambar dioptimasi — tidak ada gambar produk tanpa kompresi. |
| Keamanan | Auth berbasis session Laravel untuk dua panel terpisah; file upload hanya menerima JPG/PNG/PDF maks 2MB; stok tidak boleh negatif; brand owner hanya bisa akses data brand-nya sendiri |
| Platform | **Mobile-first (prioritas utama)** untuk public checkout — didesain dari layar 375px. Brand owner dashboard responsif (bisa diakses dari HP, dioptimalkan untuk desktop). Super admin dashboard desktop-only. |
| Browser | Chrome dan Safari mobile modern; Chrome desktop untuk dashboard |
| Skala | Kecil — MVP untuk review internal, belum ada trafik nyata |
| Aksesibilitas | Tap target minimal 44×44px untuk semua elemen interaktif di checkout page; kontras teks WCAG AA; label form eksplisit |

**Constraints:**
- Berjalan di localhost untuk fase review oleh KangNano
- Solo developer (Brams), tidak ada tim frontend/backend terpisah
- Stack terkunci: Laravel 11 + Filament v3 + Blade + Tailwind CSS + SQLite (lokal)
- File upload disimpan di Laravel local storage (`storage/app/public`)
- Hosting dan domain belum ditentukan — tidak perlu dikonfigurasi sekarang

**Asumsi yang dipakai:**
- 1 brand owner = 1 brand (bukan multi-brand per akun)
- 1 order = 1 produk (bukan cart/multi-item)
- Tidak ada varian produk
- Stok 0 = checkout diblok (customer tidak bisa submit)
- QRIS statis per brand (bukan per produk) — brand owner upload satu gambar QR
- Slug produk unik per brand (composite unique: `brand_id` + `slug`), bukan unik global
- `brand_id` disimpan langsung di tabel `orders` untuk efisiensi query dan proteksi data historis
- Checkout page menggunakan palette warna Darinari default untuk semua brand di MVP (bukan warna dinamis per brand)

---

## 3. Core Features

### Fitur 1 — Public Checkout Page

- **Prioritas:** Must
- **Deskripsi:** Halaman yang diakses customer via link langsung di HP. Menampilkan info produk dan form order lengkap. Tidak memerlukan login. Didesain mobile-first.
- **Aktor:** Customer
- **URL pattern:** `/{brand-slug}/{product-slug}`
- **Input:**
  - Nama lengkap (text, wajib)
  - Nomor WhatsApp (text, wajib, format: diawali `08` atau `+62`, max 15 karakter)
  - Alamat lengkap (textarea, wajib, min 10 karakter)
  - Pilihan kurir: JNE Reguler / J&T Express / SiCepat BEST (select, wajib)
  - Jumlah (integer, wajib, min 1, max = stok tersedia)
  - Metode pembayaran: Transfer Bank / QRIS (radio/toggle, wajib)
  - Bukti pembayaran (file upload, JPG/PNG/PDF, maks 2MB, wajib)
- **Output:**
  - Order tersimpan di DB dengan status `pending`
  - Stok produk berkurang sejumlah quantity yang dipesan
  - Halaman konfirmasi sukses ditampilkan ke customer
  - Tombol "Hubungi via WhatsApp" ditampilkan di halaman konfirmasi — customer yang menekan tombol ini yang membuka wa.me (bukan auto-open)
- **Aturan bisnis:**
  - Jika stok produk = 0, halaman menampilkan pesan "Produk sedang habis" dan form dinonaktifkan
  - Jika brand tidak aktif (`is_active = false`), halaman menampilkan 404
  - Jika produk tidak aktif (`is_active = false`), halaman menampilkan 404
  - Total harga = harga produk × jumlah (dihitung dan ditampilkan real-time via Alpine.js, divalidasi ulang di backend)
  - Stok dikurangi saat order berhasil tersimpan, bukan saat dikonfirmasi
  - Informasi pembayaran (rekening bank atau gambar QRIS) tampil dinamis sesuai pilihan metode pembayaran
  - Jika brand belum setup rekening bank, opsi Transfer Bank tidak ditampilkan
  - Jika brand belum upload QRIS, opsi QRIS tidak ditampilkan
  - Minimal satu metode pembayaran harus tersedia — jika keduanya kosong, checkout page menampilkan pesan "Halaman belum siap" (bukan 404)
- **Acceptance criteria:**
  - [ ] Seluruh halaman terbaca dan bisa diisi dengan nyaman di layar 375px tanpa horizontal scroll
  - [ ] Semua tap target (tombol, radio, select, upload) minimal 44px tingginya
  - [ ] Form dengan field kosong tidak bisa disubmit; error muncul inline di bawah field terkait
  - [ ] Setelah submit berhasil, stok produk berkurang sesuai quantity di DB
  - [ ] File bukti bayar tersimpan di storage dan path-nya tercatat di DB
  - [ ] Halaman konfirmasi menampilkan kode order, nama produk, jumlah, total, dan metode bayar
  - [ ] Tombol WhatsApp di halaman konfirmasi membuka wa.me dengan pesan pre-filled yang lengkap
  - [ ] Stok 0 menonaktifkan form dan menampilkan pesan yang jelas
  - [ ] URL brand/produk tidak valid atau tidak aktif menampilkan 404

---

### Fitur 2 — Notifikasi WhatsApp ke Brand Owner

- **Prioritas:** Must
- **Deskripsi:** Di halaman konfirmasi setelah order berhasil, customer disediakan tombol WhatsApp yang membuka `wa.me` dengan pesan pre-filled berisi ringkasan order. Customer yang menekan tombol — ini menghindari masalah browser memblok auto-open dan lebih sesuai konteks: customer memang sudah biasa berkomunikasi via WhatsApp.
- **Aktor:** Customer (action), sistem (generate link)
- **Input:** Data order yang baru dibuat
- **Output:** Tombol "Konfirmasi pesanan via WhatsApp" di halaman konfirmasi dengan link `wa.me?phone={nomor}&text={pesan}`
- **Format pesan pre-filled:**
  ```
  Halo, saya ingin konfirmasi pesanan saya:

  Kode Order: {order_code}
  Produk: {nama_produk}
  Jumlah: {quantity}
  Total: Rp {total_price}
  Pembayaran: {metode}
  Kurir: {kurir}

  Nama: {customer_name}
  Alamat: {customer_address}

  Bukti transfer sudah saya upload. Mohon dikonfirmasi. Terima kasih!
  ```
- **Aturan bisnis:**
  - Nomor tujuan adalah `whatsapp_number` dari tabel `brands`, diformat tanpa `+` dan tanpa spasi (contoh: `6281234567890`)
  - Pesan di-URL-encode sebelum dimasukkan ke link
- **Acceptance criteria:**
  - [ ] Tombol WhatsApp muncul di halaman konfirmasi setelah order berhasil
  - [ ] Link membuka WhatsApp/wa.me dengan nomor brand owner yang benar
  - [ ] Pesan sudah terisi lengkap dan terbaca dengan baik di HP

---

### Fitur 3 — Brand Owner Dashboard: Kelola Order

- **Prioritas:** Must
- **Deskripsi:** Brand owner bisa melihat semua order yang masuk, melihat bukti pembayaran, dan mengubah status order.
- **Aktor:** Brand owner (login via `/brand/login`)
- **Input:** Aksi status update, filter status
- **Output:** Status order ter-update, link wa.me ke customer tersedia saat status dikonfirmasi
- **Aturan bisnis:**
  - Brand owner hanya bisa melihat order milik brand-nya sendiri (enforced via Filament Policy)
  - Transisi status yang diizinkan: `pending → confirmed`, `confirmed → processing`, `processing → shipped`, `shipped → done`, status apapun → `cancelled`
  - Status tidak bisa mundur kecuali ke `cancelled`
  - Saat mengubah status ke `confirmed`, sistem menampilkan link `wa.me` untuk notifikasi ke customer (sebagai action button di Filament)
- **Acceptance criteria:**
  - [ ] Brand owner tidak bisa mengakses order brand lain (403 jika dicoba via URL)
  - [ ] Perubahan status tersimpan dan langsung tercermin di daftar
  - [ ] Bukti pembayaran bisa dilihat/dibuka (preview atau link download) dari detail order
  - [ ] Filter berdasarkan status berfungsi
  - [ ] Tombol wa.me ke customer muncul di action saat status = `confirmed`

---

### Fitur 4 — Brand Owner Dashboard: Kelola Produk

- **Prioritas:** Must
- **Deskripsi:** Brand owner bisa menambah, mengedit, menonaktifkan produk, dan melihat/update stok.
- **Aktor:** Brand owner (login)
- **Input:** Data produk (nama, deskripsi, harga, stok, gambar, status aktif)
- **Output:** Produk tersimpan/terupdate, checkout page mencerminkan perubahan
- **Aturan bisnis:**
  - Slug produk di-generate otomatis dari nama produk menggunakan `Str::slug()` saat create; dapat diedit manual
  - Slug harus unik per brand — `UNIQUE(brand_id, slug)`
  - Harga harus integer positif (Rupiah, tanpa desimal, tanpa format titik/koma saat input)
  - Stok tidak boleh diset ke nilai negatif
  - Produk yang dinonaktifkan menampilkan 404 di checkout page
  - Gambar produk disimpan di `storage/app/public/products/{brand_id}/`
- **Acceptance criteria:**
  - [ ] Produk baru muncul di checkout page setelah disimpan dan diaktifkan
  - [ ] Slug duplikat dalam brand yang sama ditolak dengan pesan error yang jelas
  - [ ] Gambar produk tersimpan dan ditampilkan di checkout page
  - [ ] Stok bisa diupdate manual dari dashboard
  - [ ] Input harga negatif ditolak di level validasi

---

### Fitur 5 — Brand Owner Dashboard: Setting Brand

- **Prioritas:** Must
- **Deskripsi:** Brand owner bisa mengatur identitas brand dan informasi pembayaran yang ditampilkan di checkout page.
- **Aktor:** Brand owner (login)
- **Input:**
  - Logo brand (file upload, JPG/PNG, maks 2MB, wajib)
  - Nama brand (text, wajib — tidak bisa diubah sendiri oleh brand owner, hanya super admin)
  - Nomor WhatsApp brand (text, wajib — untuk notifikasi order masuk)
  - Nama bank (text)
  - Nomor rekening (text)
  - Nama pemilik rekening (text)
  - Gambar QRIS (file upload, JPG/PNG, maks 2MB)
- **Output:** Data brand terupdate, checkout page mencerminkan perubahan
- **Aturan bisnis:**
  - Minimal salah satu metode pembayaran (bank atau QRIS) harus terisi agar checkout page bisa aktif
  - Slug brand tidak bisa diubah oleh brand owner (hanya super admin) — slug adalah bagian dari URL publik
  - Nomor WhatsApp disimpan dalam format internasional tanpa `+` (sistem auto-strip `+` dan `0` di depan jika input `08xx`)
  - Logo disimpan di `storage/app/public/logos/{brand_id}/`
  - QRIS disimpan di `storage/app/public/qris/{brand_id}/`
- **Acceptance criteria:**
  - [ ] Logo baru langsung tampil di checkout page setelah disimpan
  - [ ] Info rekening/QRIS tampil sesuai metode yang dipilih customer di checkout page
  - [ ] Nomor WA tersimpan dalam format yang kompatibel dengan `wa.me`
  - [ ] Jika rekening dan QRIS kosong, muncul warning di dashboard (bukan silent fail)

---

### Fitur 6 — Brand Owner Dashboard: Ganti Password

- **Prioritas:** Must
- **Deskripsi:** Brand owner bisa mengubah password akun sendiri dari dashboard tanpa perlu minta bantuan super admin.
- **Aktor:** Brand owner (login)
- **Input:** Password lama, password baru, konfirmasi password baru
- **Output:** Password terupdate
- **Aturan bisnis:**
  - Password lama harus diverifikasi sebelum password baru disimpan
  - Password baru minimal 8 karakter
  - Password baru dan konfirmasi harus sama
- **Acceptance criteria:**
  - [ ] Password berhasil diubah dan session tetap aktif setelah perubahan
  - [ ] Input password lama yang salah menampilkan error
  - [ ] Password baru < 8 karakter ditolak

---

### Fitur 7 — Brand Owner Dashboard: Link Checkout

- **Prioritas:** Should
- **Deskripsi:** Halaman yang menampilkan semua link checkout per produk aktif, siap di-copy dan dibagikan ke customer via WhatsApp.
- **Aktor:** Brand owner (login)
- **Input:** Tidak ada (data diambil dari produk aktif milik brand)
- **Output:** Daftar link per produk dengan tombol copy (clipboard API)
- **Aturan bisnis:**
  - Hanya produk aktif (`is_active = true`) yang muncul
  - Link yang ditampilkan adalah URL lengkap yang bisa langsung dibuka
- **Acceptance criteria:**
  - [ ] Tombol copy menyalin URL yang benar ke clipboard
  - [ ] Hanya produk aktif yang muncul di daftar
  - [ ] Jika belum ada produk aktif, tampilkan empty state dengan instruksi

---

### Fitur 8 — Super Admin Dashboard: Kelola Brand Owner

- **Prioritas:** Must
- **Deskripsi:** Tim Darinari bisa mendaftarkan brand owner baru, mengaktifkan/menonaktifkan akun, dan memonitor aktivitas.
- **Aktor:** Super admin (login via `/admin/login`)
- **Input saat create:** Nama, email, password, nama brand, slug brand, nomor WA brand
- **Output:** Akun brand owner terbuat dengan brand terkait, brand owner bisa login
- **Aturan bisnis:**
  - Slug brand harus unik secara global
  - Brand owner yang dinonaktifkan (`is_active = false`) tidak bisa login; checkout page menampilkan 404
  - Super admin tidak bisa menghapus brand owner yang memiliki order — hanya bisa menonaktifkan
  - Super admin bisa reset password brand owner (set password baru tanpa verifikasi password lama)
  - Saat create brand owner, sistem otomatis membuat record `brands` yang terasosiasi
- **Acceptance criteria:**
  - [ ] Brand owner yang baru dibuat bisa langsung login
  - [ ] Menonaktifkan brand owner memblok login dan checkout page seketika
  - [ ] Super admin bisa melihat jumlah order dan jumlah produk per brand owner di daftar
  - [ ] Upaya hapus brand owner yang punya order menghasilkan error yang jelas

---

### Fitur 9 — Super Admin Dashboard: Monitor Semua Order

- **Prioritas:** Should
- **Deskripsi:** Super admin bisa melihat semua order dari semua brand dalam satu tampilan, termasuk bukti pembayaran.
- **Aktor:** Super admin (login)
- **Input:** Filter berdasarkan brand, status, rentang tanggal
- **Output:** Daftar order lintas brand dengan akses ke detail dan bukti bayar
- **Aturan bisnis:**
  - Super admin bisa melihat bukti pembayaran dari semua order (tidak dibatasi per brand)
  - Super admin tidak bisa mengubah status order (hanya brand owner yang bisa)
- **Acceptance criteria:**
  - [ ] Order dari semua brand tampil dalam satu tabel
  - [ ] Filter brand, status, dan tanggal berfungsi dan hasil akurat
  - [ ] Super admin bisa membuka/preview bukti pembayaran

---

## 4. User Flow

### Flow utama: Customer melakukan order

1. Brand owner mengirim link produk ke customer via WhatsApp (`/{brand-slug}/{product-slug}`)
2. Customer membuka link di HP → checkout page tampil dengan info produk dan form
3. Customer mengisi form: nama, nomor WA, alamat
4. Customer memilih kurir dan jumlah → total harga update real-time
5. Customer memilih metode pembayaran → info rekening atau gambar QRIS ditampilkan
6. Customer melakukan transfer/scan QRIS di luar sistem
7. Customer upload bukti pembayaran → tap "Kirim pesanan"
8. Sistem menyimpan order (dalam DB transaction), mengurangi stok, generate kode order
9. Halaman konfirmasi tampil: kode order, ringkasan, tombol "Konfirmasi via WhatsApp"
10. Customer tap tombol WhatsApp → WhatsApp terbuka dengan pesan pre-filled ke brand owner
11. Brand owner terima pesan → login ke dashboard → cek bukti bayar → konfirmasi order

### Flow utama: Brand owner mengelola order

1. Brand owner login ke `/brand/login`
2. Dashboard tampil: metrik ringkas (order masuk, pending, total bulan ini)
3. Brand owner tap/klik order status `pending`
4. Buka detail order → lihat bukti bayar
5. Tap "Konfirmasi" → status berubah ke `confirmed`
6. Tombol wa.me ke customer tersedia → opsional ditekan untuk notifikasi ke customer
7. Lanjut workflow sesuai proses: `processing → shipped → done`

### Flow utama: Super admin mendaftarkan brand owner baru

1. Super admin login ke `/admin/login`
2. Masuk ke menu "Brand Owner" → klik "Tambah baru"
3. Isi data: nama, email, password, nama brand, slug brand, nomor WA
4. Simpan → akun brand owner dan record brand otomatis terbuat
5. Kredensial diberikan ke brand owner secara manual (di luar sistem — misal via WhatsApp)
6. Brand owner login → setup logo, info pembayaran, dan produk pertama

### Flow error / alternatif

| Kondisi | Respons sistem | Yang dilihat customer/user |
|---------|----------------|---------------------------|
| Stok = 0 saat halaman dibuka | Form dinonaktifkan | "Produk sedang habis" — form tidak bisa diisi |
| Stok habis antara buka halaman dan submit | Validasi backend tolak, DB transaction rollback | "Stok tidak mencukupi. Silakan refresh halaman." |
| Brand/produk tidak aktif atau tidak ditemukan | 404 | Halaman 404 sederhana |
| Brand belum setup pembayaran | Checkout page tidak aktif | "Halaman belum siap, silakan hubungi penjual." |
| File upload > 2MB atau format salah | 422, validasi per field | "Format tidak didukung / Ukuran maksimal 2MB" |
| Form submit dengan field kosong | 422, data tidak tersimpan | Error inline di bawah field yang bermasalah |
| Brand owner akses order brand lain | 403 | Redirect ke dashboard |
| DB transaction gagal | 500, rollback total — stok tidak berkurang | "Terjadi kesalahan. Silakan coba lagi." |

---

## 5. Data / Content Model

### Entitas: `users`

| Field | Tipe | Wajib? | Catatan |
|-------|------|--------|---------|
| `id` | `bigint, PK, auto-increment` | ya | |
| `name` | `string(255)` | ya | |
| `email` | `string(255)` | ya | UNIQUE |
| `password` | `string` | ya | bcrypt via Laravel Hash |
| `role` | `enum('super_admin','brand_owner')` | ya | |
| `is_active` | `boolean` | ya | default: `true` |
| `created_at` | `timestamp` | ya | |
| `updated_at` | `timestamp` | ya | |

---

### Entitas: `brands`

| Field | Tipe | Wajib? | Catatan |
|-------|------|--------|---------|
| `id` | `bigint, PK, auto-increment` | ya | |
| `user_id` | `bigint, FK → users.id` | ya | one-to-one |
| `name` | `string(255)` | ya | hanya bisa diubah oleh super admin |
| `slug` | `string(255)` | ya | UNIQUE global; kebab-case; bagian dari URL publik |
| `logo_path` | `string` | tidak | path relatif di storage |
| `bank_name` | `string(100)` | tidak | |
| `bank_account_number` | `string(50)` | tidak | |
| `bank_account_name` | `string(255)` | tidak | |
| `qris_image_path` | `string` | tidak | path relatif di storage |
| `whatsapp_number` | `string(20)` | ya | format internasional tanpa `+`, contoh: `6281234567890` |
| `is_active` | `boolean` | ya | default: `true`; hanya diubah oleh super admin |
| `created_at` | `timestamp` | ya | |
| `updated_at` | `timestamp` | ya | |

**Relasi:** `brands.user_id` → `users.id` (one-to-one)
**Catatan:** Kolom `primary_color` dan `secondary_color` tidak dibuat di MVP — masuk fase berikutnya.

---

### Entitas: `products`

| Field | Tipe | Wajib? | Catatan |
|-------|------|--------|---------|
| `id` | `bigint, PK, auto-increment` | ya | |
| `brand_id` | `bigint, FK → brands.id` | ya | |
| `name` | `string(255)` | ya | |
| `slug` | `string(255)` | ya | UNIQUE per brand: `UNIQUE(brand_id, slug)` |
| `description` | `text` | tidak | |
| `price` | `unsignedInteger` | ya | dalam Rupiah; tidak boleh 0 |
| `stock` | `unsignedInteger` | ya | min 0; tidak boleh negatif |
| `image_path` | `string` | tidak | path relatif di storage |
| `is_active` | `boolean` | ya | default: `true` |
| `created_at` | `timestamp` | ya | |
| `updated_at` | `timestamp` | ya | |

**Relasi:** `products.brand_id` → `brands.id`
**Constraint penting:** `UNIQUE(brand_id, slug)` — slug unik per brand, **bukan** unik global.

---

### Entitas: `orders`

| Field | Tipe | Wajib? | Catatan |
|-------|------|--------|---------|
| `id` | `bigint, PK, auto-increment` | ya | |
| `product_id` | `bigint, FK → products.id` | ya | |
| `brand_id` | `bigint, FK → brands.id` | ya | denormalized — query efisien + proteksi historis |
| `order_code` | `string(50)` | ya | UNIQUE; format: `ORD-YYYYMMDD-XXXXX` (5 digit random uppercase alphanumeric) |
| `customer_name` | `string(255)` | ya | |
| `customer_phone` | `string(20)` | ya | nomor WA customer; diformat saat simpan |
| `customer_address` | `text` | ya | |
| `courier` | `enum('jne','jnt','sicepat')` | ya | |
| `payment_method` | `enum('bank_transfer','qris')` | ya | |
| `payment_proof_path` | `string` | ya | path relatif file bukti bayar di storage |
| `quantity` | `unsignedInteger` | ya | min 1 |
| `total_price` | `unsignedBigInteger` | ya | dalam Rupiah; dihitung dan divalidasi backend |
| `status` | `enum('pending','confirmed','processing','shipped','done','cancelled')` | ya | default: `pending` |
| `created_at` | `timestamp` | ya | |
| `updated_at` | `timestamp` | ya | |

**Relasi:**
- `orders.product_id` → `products.id`
- `orders.brand_id` → `brands.id`

---

## 6. Architecture & Tech

| Layer | Teknologi |
|-------|-----------|
| Framework | Laravel 11 |
| Dashboard panels | Filament v3 (multi-panel) |
| Public frontend | Blade + Tailwind CSS + Alpine.js (via Filament) |
| Database (lokal/MVP) | SQLite |
| Database (hosting, fase berikutnya) | MySQL 8+ |
| Auth | Laravel session-based (Filament bawaan per panel) |
| File storage | Laravel `local` disk → `storage/app/public` → symlink via `php artisan storage:link` |
| Dev server | `php artisan serve` atau Laravel Herd |

**Struktur Filament panel:**

| Panel | Path login | Guard | Akses |
|-------|------------|-------|-------|
| `AdminPanelProvider` | `/admin/login` | `web` (role = super_admin) | Super admin only |
| `BrandPanelProvider` | `/brand/login` | `web` (role = brand_owner, is_active = true) | Brand owner only |

**Tidak ada REST API** — tidak ada frontend terpisah, tidak ada mobile app. Semua via Blade (checkout page) dan Filament (dashboard). Alpine.js digunakan untuk interaktivitas ringan di checkout page (hitung total real-time, tampil/sembunyikan info pembayaran).

**Integrasi pihak ketiga:** Tidak ada. WhatsApp via `wa.me` link statis yang di-generate server-side.

---

## 7. Design & Technical Constraints

### Konvensi kode

- **Library wajib:** Laravel 11, Filament v3, Tailwind CSS, Alpine.js
- **Library yang dilarang:** Tidak ada payment gateway, tidak ada WA API SDK, tidak ada chart library berat untuk MVP
- **Pola yang harus diikuti:**
  - Semua CRUD dashboard via Filament Resource (bukan custom Blade + controller)
  - Isolasi data brand owner via Filament `Policy` — jangan filter manual di controller
  - Validasi checkout via Laravel `Form Request` (bukan inline di controller)
  - Stok dikurangi dalam `DB::transaction()` bersamaan dengan penyimpanan order — tidak boleh dipisah
  - `order_code` di-generate di `OrderService` atau model `boot()` — bukan di controller
  - Semua akses file (upload, preview) melalui `Storage` facade — tidak ada direct path `public/`
- **Penamaan:**
  - Tabel: `snake_case` plural
  - Model: `PascalCase` singular
  - Filament Resource: ikuti konvensi default Filament (`ProductResource`, `OrderResource`, dll.)
  - Route checkout: `/{brand:slug}/{product:slug}` (route model binding)

### Design — Public Checkout Page (mobile-first, prioritas utama)

Checkout page adalah satu-satunya permukaan yang dilihat customer. Mayoritas customer membukanya langsung dari chat WhatsApp di HP. Ini bukan halaman Darinari — ini halaman milik brand. Customer harus merasa familiar dan percaya.

**Prinsip desain:**
- **Viewport default:** 375px (iPhone SE) — semua elemen harus berfungsi di lebar ini tanpa horizontal scroll
- **Tap target:** Semua elemen interaktif (tombol, radio, select, file upload) minimal 44×44px
- **Single column layout:** Tidak ada layout 2-kolom di checkout page — semua konten mengalir vertikal
- **Typography:** System font stack (`-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`) — tidak ada Google Fonts. Ukuran body minimal 16px agar tidak ada auto-zoom di iOS saat focus input
- **Warna MVP:** Palette Darinari default untuk semua brand. Tidak ada dynamic theming di MVP.
  - Primary: `#1D9E75` (teal — warna aksi utama, tombol submit, aksen)
  - Background: `#F8F9FA`
  - Surface card: `#FFFFFF`
  - Text primary: `#1C1C1E`
  - Text secondary: `#6B7280`
  - Border: `#E5E7EB`
  - Error: `#EF4444`
  - Success: `#10B981`
- **Signature element:** Header checkout page menampilkan logo brand + nama brand secara prominent — ini bukan halaman generik. Customer yang membuka link harus langsung tahu ini halaman milik brand yang mereka kenal.
- **Info pembayaran:** Tampil/hilang dinamis via Alpine.js sesuai pilihan metode — tidak ada reload halaman
- **QRIS:** Gambar QR ditampilkan cukup besar (min 200×200px) agar bisa di-scan langsung dari layar HP
- **State error:** Inline di bawah field terkait, bukan alert box di atas form
- **Animasi:** Tidak ada animasi berat. Hanya CSS transition pada focus state input (`ring`) dan toggle pembayaran

**Design — Dashboard (Filament default theme):**
- Gunakan Filament default theme tanpa custom styling — konsisten, maintainable, cepat dibangun
- Brand owner dashboard harus bisa dioperasikan dari HP (Filament v3 sudah responsif)
- Fokus pada konfigurasi Filament yang benar, bukan estetika custom

### Error & edge case handling

| Kondisi | Respons sistem | Pesan ke user |
|---------|----------------|---------------|
| Field form kosong saat submit | 422, tidak tersimpan | Error inline per field: "Nama tidak boleh kosong" dll. |
| Stok tidak mencukupi saat submit | 422, rollback, stok tidak berkurang | "Stok tidak mencukupi. Silakan refresh halaman." |
| Brand/produk tidak aktif atau tidak ditemukan | 404 | Halaman 404 minimal dengan pesan singkat |
| Brand belum setup pembayaran | Checkout page return pesan khusus (bukan 404) | "Halaman belum siap. Silakan hubungi penjual." |
| File upload format/ukuran salah | 422 | "Format tidak didukung. Gunakan JPG, PNG, atau PDF maks 2MB." |
| Brand owner akses order brand lain via URL | 403 | Redirect ke dashboard, tanpa pesan teknis |
| DB transaction gagal | 500, rollback total | "Terjadi kesalahan. Silakan coba lagi." |
| `order_code` collision | Retry generate maksimal 3× hingga unique | Transparan ke user |
| Input harga/stok negatif di dashboard | 422 Filament validation | Pesan error per field di form Filament |

---

## 8. Open Questions / `[KONFIRMASI]`

Semua open questions dari v1.0 telah ditutup. Keputusan dan alasannya:

| No | Pertanyaan (v1.0) | Keputusan Final | Alasan |
|----|-------------------|-----------------|--------|
| 1 | Warna brand dinamis di MVP? | **Tidak — fase berikutnya.** Checkout page pakai palette Darinari default. | Menambah kompleksitas (CSS injection dari DB) tanpa nilai yang proporsional untuk MVP. Brand owner baru tidak punya brand guideline yang established. Bisa ditambah di fase 2 dengan minimal rework karena warna sudah di-tokenize sebagai CSS variable. |
| 2 | Path panel Filament? | **`/admin` dan `/brand`** | Pendek, jelas, tidak ambigu. Tidak ada konflik dengan route checkout `/{brand-slug}/{product-slug}`. |
| 3 | Format `order_code`? | **`ORD-YYYYMMDD-XXXXX`** (5 digit alphanumeric uppercase random) | Readable oleh manusia, cukup unik untuk skala MVP, mudah disebut di WA. Contoh: `ORD-20260612-A3F7K`. |
| 4 | Brand owner bisa ganti password sendiri? | **Ya — bisa.** Fitur di Filament Profile page. | Brand owner adalah pengguna non-teknis. Kalau harus minta super admin setiap kali lupa/ganti password, friction terlalu tinggi dan menyulitkan operasional Darinari. |
| 5 | WA notifikasi auto-open atau tombol? | **Tombol di halaman konfirmasi** (bukan auto-open). | Browser mobile modern (Chrome, Safari) memblok `window.open()` yang tidak dipicu user gesture secara langsung. Auto-open tidak reliable dan bisa dianggap spam behavior. Tombol lebih bersih, lebih eksplisit, dan customer memang sudah terbiasa tap tombol WA. |
| 6 | Super admin bisa lihat bukti bayar? | **Ya.** Super admin bisa lihat dan preview bukti pembayaran semua order. | Darinari perlu kemampuan audit dan monitoring penuh atas semua transaksi di platform mereka. Membatasi akses super admin ke bukti bayar justru menyulitkan operasional internal. |
| 7 | URL struktur saat hosting: subdomain atau path-based? | **Path-based untuk sekarang** (`domain.id/{brand-slug}/{product-slug}`). | Subdomain per brand membutuhkan wildcard DNS dan konfigurasi server yang lebih kompleks — tidak perlu untuk MVP. Path-based sudah cukup dan mudah dimigrasi ke subdomain di fase hosting jika diperlukan. |

**Tidak ada open questions tersisa.** PRD ini siap digunakan sebagai brief code-gen.

---

*Living document. Update saat ada keputusan signifikan. Detail fungsional hanya di Section 3.*
