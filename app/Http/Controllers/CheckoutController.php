<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Support\WhatsApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public const COURIER_LABELS = [
        'jne' => 'JNE Reguler',
        'jnt' => 'J&T Express',
        'sicepat' => 'SiCepat BEST',
    ];

    public const PAYMENT_METHOD_LABELS = [
        'bank_transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
    ];

    public function show(Brand $brand, Product $product)
    {
        abort_unless($brand->is_active, 404);
        abort_unless($product->is_active, 404);

        $paymentNotReady = blank($brand->bank_account_number) && blank($brand->qris_image_path);

        return view('checkout.show', [
            'brand' => $brand,
            'product' => $product,
            'paymentNotReady' => $paymentNotReady,
        ]);
    }

    public function submit(StoreOrderRequest $request, Brand $brand, Product $product)
    {
        abort_unless($brand->is_active, 404);
        abort_unless($product->is_active, 404);

        $validated = $request->validated();

        $order = DB::transaction(function () use ($request, $validated, $brand, $product) {
            $lockedProduct = Product::lockForUpdate()->findOrFail($product->id);

            if ($lockedProduct->stock < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi. Silakan refresh halaman.',
                ]);
            }

            $proofPath = $request->file('payment_proof')->store("payments/{$brand->id}", 'local');

            $order = Order::create([
                'product_id' => $lockedProduct->id,
                'brand_id' => $brand->id,
                'order_code' => OrderService::generateCode(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => WhatsApp::normalize($validated['customer_phone']),
                'customer_address' => $validated['customer_address'],
                'courier' => $validated['courier'],
                'payment_method' => $validated['payment_method'],
                'payment_proof_path' => $proofPath,
                'quantity' => $validated['quantity'],
                'total_price' => $lockedProduct->price * $validated['quantity'],
                'status' => 'pending',
            ]);

            $lockedProduct->decrement('stock', $validated['quantity']);

            return $order;
        });

        return redirect()->route('checkout.success', [
            'brand' => $brand->slug,
            'product' => $product->slug,
            'order' => $order->order_code,
        ]);
    }

    public function success(Brand $brand, Product $product, Order $order)
    {
        $message = "Halo, saya ingin konfirmasi pesanan saya:\n\n"
            . "Kode Order: {$order->order_code}\n"
            . "Produk: {$product->name}\n"
            . "Jumlah: {$order->quantity}\n"
            . 'Total: Rp ' . number_format($order->total_price, 0, ',', '.') . "\n"
            . 'Pembayaran: ' . self::PAYMENT_METHOD_LABELS[$order->payment_method] . "\n"
            . 'Kurir: ' . self::COURIER_LABELS[$order->courier] . "\n\n"
            . "Nama: {$order->customer_name}\n"
            . "Alamat: {$order->customer_address}\n\n"
            . 'Bukti transfer sudah saya upload. Mohon dikonfirmasi. Terima kasih!';

        $whatsappLink = WhatsApp::link($brand->whatsapp_number, $message);

        return view('checkout.success', [
            'brand' => $brand,
            'product' => $product,
            'order' => $order,
            'whatsappLink' => $whatsappLink,
            'courierLabel' => self::COURIER_LABELS[$order->courier],
            'paymentMethodLabel' => self::PAYMENT_METHOD_LABELS[$order->payment_method],
        ]);
    }
}
