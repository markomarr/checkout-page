<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil - {{ $brand->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 16px;
            background-color: #F8F9FA;
            color: #1C1C1E;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-[480px] mx-auto px-4 py-6 space-y-4">

        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-6 text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-[#10B981] text-white flex items-center justify-center text-3xl">
                &check;
            </div>
            <h1 class="text-lg font-bold">Pesanan berhasil dikirim!</h1>

            <div class="rounded-lg bg-[#F8F9FA] border border-[#E5E7EB] py-3">
                <p class="text-sm text-[#6B7280]">Kode Order</p>
                <p class="text-xl font-bold text-[#1D9E75] tracking-wide">{{ $order->order_code }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#E5E7EB] p-4 space-y-2">
            <h2 class="font-semibold">Ringkasan Pesanan</h2>

            <div class="flex justify-between text-sm">
                <span class="text-[#6B7280]">Produk</span>
                <span>{{ $product->name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-[#6B7280]">Jumlah</span>
                <span>{{ $order->quantity }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-[#6B7280]">Total</span>
                <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-[#6B7280]">Pembayaran</span>
                <span>{{ $paymentMethodLabel }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-[#6B7280]">Kurir</span>
                <span>{{ $courierLabel }}</span>
            </div>
        </div>

        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
            class="block w-full min-h-[44px] rounded-lg bg-[#1D9E75] text-white font-semibold text-center py-3">
            Konfirmasi via WhatsApp
        </a>
    </div>
</body>
</html>
