<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;
use RuntimeException;

class OrderService
{
    /**
     * Generate a unique order code in the format ORD-YYYYMMDD-XXXXX.
     */
    public static function generateCode(): string
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $code = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            if (! Order::where('order_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Gagal membuat kode order unik, silakan coba lagi.');
    }
}
