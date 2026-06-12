<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} - {{ $brand->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 16px;
            background-color: #F8F9FA;
            color: #1C1C1E;
        }
        input, select, textarea, button {
            font-size: 16px;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-[480px] mx-auto px-4 py-6">

        {{-- Header brand --}}
        <header class="flex items-center gap-3 mb-6">
            @if($brand->logo_path)
                <img src="{{ Storage::url($brand->logo_path) }}" alt="{{ $brand->name }}" class="w-12 h-12 rounded-full object-cover border border-[#E5E7EB]">
            @else
                <div class="w-12 h-12 rounded-full bg-[#1D9E75] text-white flex items-center justify-center font-bold text-lg">
                    {{ Str::substr($brand->name, 0, 1) }}
                </div>
            @endif
            <h1 class="text-lg font-bold text-[#1C1C1E]">{{ $brand->name }}</h1>
        </header>

        @if($paymentNotReady)
            <div class="bg-white rounded-xl p-6 text-center shadow-sm border border-[#E5E7EB]">
                <p class="text-[#1C1C1E] font-medium">Halaman belum siap, silakan hubungi penjual.</p>
            </div>
        @else
            @php
                $outOfStock = $product->stock < 1;
                $hasBankTransfer = ! blank($brand->bank_account_number);
                $hasQris = ! blank($brand->qris_image_path);
                $defaultPaymentMethod = old('payment_method', $hasBankTransfer ? 'bank_transfer' : 'qris');
            @endphp

            <div
                x-data="checkout({
                    price: {{ $product->price }},
                    stock: {{ $product->stock }},
                    quantity: {{ old('quantity', 1) }},
                    paymentMethod: '{{ $defaultPaymentMethod }}',
                })"
                class="space-y-4"
            >
                {{-- Card produk --}}
                <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] overflow-hidden relative">
                    @if($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-4">
                        <h2 class="font-semibold text-lg">{{ $product->name }}</h2>
                        @if($product->description)
                            <p class="text-sm text-[#6B7280] mt-1">{{ $product->description }}</p>
                        @endif
                        <p class="text-[#1D9E75] font-bold text-xl mt-2">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <p class="text-sm text-[#6B7280]">Stok tersisa: {{ $product->stock }}</p>
                    </div>

                    @if($outOfStock)
                        <div class="absolute inset-0 bg-white/95 flex items-center justify-center">
                            <p class="text-[#EF4444] font-semibold text-lg">Produk sedang habis</p>
                        </div>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('checkout.submit', ['brand' => $brand->slug, 'product' => $product->slug]) }}"
                    enctype="multipart/form-data"
                    class="space-y-4"
                >
                    @csrf

                    <fieldset @if($outOfStock) disabled @endif class="space-y-4">

                        {{-- Data kamu --}}
                        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4 space-y-3">
                            <h3 class="font-semibold">Data kamu</h3>

                            <div>
                                <label for="customer_name" class="block text-sm font-medium mb-1">Nama Lengkap</label>
                                <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}"
                                    class="w-full min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 focus:outline-none focus:ring-2 focus:ring-[#1D9E75] transition"
                                    placeholder="Masukkan nama lengkap">
                                @error('customer_name')
                                    <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-medium mb-1">Nomor WhatsApp</label>
                                <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}"
                                    class="w-full min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 focus:outline-none focus:ring-2 focus:ring-[#1D9E75] transition"
                                    placeholder="08xxxxxxxxxx">
                                @error('customer_phone')
                                    <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_address" class="block text-sm font-medium mb-1">Alamat Lengkap</label>
                                <textarea id="customer_address" name="customer_address" rows="3"
                                    class="w-full min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1D9E75] transition"
                                    placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos">{{ old('customer_address') }}</textarea>
                                @error('customer_address')
                                    <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Pengiriman --}}
                        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4 space-y-3">
                            <h3 class="font-semibold">Pengiriman</h3>

                            <div>
                                <label for="courier" class="block text-sm font-medium mb-1">Kurir</label>
                                <select id="courier" name="courier"
                                    class="w-full min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 focus:outline-none focus:ring-2 focus:ring-[#1D9E75] transition">
                                    <option value="" disabled {{ old('courier') ? '' : 'selected' }}>Pilih kurir</option>
                                    <option value="jne" {{ old('courier') === 'jne' ? 'selected' : '' }}>JNE Reguler</option>
                                    <option value="jnt" {{ old('courier') === 'jnt' ? 'selected' : '' }}>J&amp;T Express</option>
                                    <option value="sicepat" {{ old('courier') === 'sicepat' ? 'selected' : '' }}>SiCepat BEST</option>
                                </select>
                                @error('courier')
                                    <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium mb-1">Jumlah</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="decrement()"
                                        class="w-11 h-11 rounded-lg border border-[#E5E7EB] flex items-center justify-center text-lg font-semibold text-[#1C1C1E] active:bg-[#F8F9FA]">
                                        &minus;
                                    </button>
                                    <input type="number" id="quantity" name="quantity" x-model.number="quantity"
                                        min="1" max="{{ $product->stock }}" inputmode="numeric"
                                        class="w-full min-h-[44px] text-center rounded-lg border border-[#E5E7EB] px-3 focus:outline-none focus:ring-2 focus:ring-[#1D9E75] transition">
                                    <button type="button" @click="increment()"
                                        class="w-11 h-11 rounded-lg border border-[#E5E7EB] flex items-center justify-center text-lg font-semibold text-[#1C1C1E] active:bg-[#F8F9FA]">
                                        &plus;
                                    </button>
                                </div>
                                @error('quantity')
                                    <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Ringkasan harga --}}
                        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[#6B7280]">Total harga</span>
                                <span class="text-[#1D9E75] font-bold text-xl" x-text="formatRupiah(total)"></span>
                            </div>
                        </div>

                        {{-- Pembayaran --}}
                        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4 space-y-3">
                            <h3 class="font-semibold">Pembayaran</h3>

                            <div class="space-y-2">
                                @if($hasBankTransfer)
                                    <label class="flex items-center gap-3 min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 cursor-pointer"
                                        :class="paymentMethod === 'bank_transfer' ? 'border-[#1D9E75] ring-1 ring-[#1D9E75]' : ''">
                                        <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="w-5 h-5 accent-[#1D9E75]">
                                        <span>Transfer Bank</span>
                                    </label>
                                @endif

                                @if($hasQris)
                                    <label class="flex items-center gap-3 min-h-[44px] rounded-lg border border-[#E5E7EB] px-3 cursor-pointer"
                                        :class="paymentMethod === 'qris' ? 'border-[#1D9E75] ring-1 ring-[#1D9E75]' : ''">
                                        <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="w-5 h-5 accent-[#1D9E75]">
                                        <span>QRIS</span>
                                    </label>
                                @endif
                            </div>
                            @error('payment_method')
                                <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                            @enderror

                            @if($hasBankTransfer)
                                <div x-show="paymentMethod === 'bank_transfer'" class="rounded-lg bg-[#F8F9FA] border border-[#E5E7EB] p-3 text-sm space-y-1">
                                    <p><span class="text-[#6B7280]">Bank:</span> {{ $brand->bank_name }}</p>
                                    <p><span class="text-[#6B7280]">No. Rekening:</span> {{ $brand->bank_account_number }}</p>
                                    <p><span class="text-[#6B7280]">Atas Nama:</span> {{ $brand->bank_account_name }}</p>
                                </div>
                            @endif

                            @if($hasQris)
                                <div x-show="paymentMethod === 'qris'" class="rounded-lg bg-[#F8F9FA] border border-[#E5E7EB] p-3 flex flex-col items-center">
                                    <img src="{{ Storage::url($brand->qris_image_path) }}" alt="QRIS {{ $brand->name }}"
                                        class="w-full max-w-[240px] aspect-square object-contain" style="min-width: 200px; min-height: 200px;">
                                    <p class="text-sm text-[#6B7280] mt-2">Scan QRIS di atas untuk membayar</p>
                                </div>
                            @endif
                        </div>

                        {{-- Upload bukti --}}
                        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4 space-y-3">
                            <h3 class="font-semibold">Bukti Pembayaran</h3>
                            <label for="payment_proof"
                                class="flex min-h-[44px] items-center justify-center rounded-lg border border-dashed border-[#E5E7EB] px-3 cursor-pointer text-[#6B7280] text-center">
                                <span>Pilih file (JPG, PNG, atau PDF, maks 2MB)</span>
                            </label>
                            <input type="file" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm">
                            @error('payment_proof')
                                <p class="text-[#EF4444] text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <button type="submit"
                            class="w-full min-h-[44px] rounded-lg bg-[#1D9E75] text-white font-semibold py-3 disabled:opacity-50">
                            Kirim Pesanan
                        </button>
                    </fieldset>

                    @if($outOfStock)
                        <p class="text-center text-[#EF4444] font-medium">Produk sedang habis</p>
                    @endif
                </form>
            </div>

            <script>
                function checkout({ price, stock, quantity, paymentMethod }) {
                    return {
                        price,
                        stock,
                        quantity: quantity && quantity >= 1 ? quantity : 1,
                        paymentMethod,
                        get total() {
                            return this.price * this.quantity;
                        },
                        increment() {
                            if (this.quantity < this.stock) {
                                this.quantity++;
                            }
                        },
                        decrement() {
                            if (this.quantity > 1) {
                                this.quantity--;
                            }
                        },
                        formatRupiah(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        },
                    };
                }
            </script>
        @endif
    </div>
</body>
</html>
