<?php

namespace App\Support;

class WhatsApp
{
    /**
     * Normalize a phone number to international format without `+`.
     * Input: 081234567890 / +6281234567890 / 6281234567890
     * Output: 6281234567890
     */
    public static function normalize(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    /**
     * Build a wa.me link with a pre-filled, URL-encoded message.
     */
    public static function link(string $number, string $message): string
    {
        return 'https://wa.me/' . self::normalize($number) . '?text=' . rawurlencode($message);
    }
}
