<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');
        $brand = $this->route('brand');

        $availablePaymentMethods = [];

        if (! blank($brand->bank_account_number)) {
            $availablePaymentMethods[] = 'bank_transfer';
        }

        if (! blank($brand->qris_image_path)) {
            $availablePaymentMethods[] = 'qris';
        }

        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
            'customer_address' => ['required', 'string', 'min:10'],
            'courier' => ['required', 'in:jne,jnt,sicepat'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . max($product->stock, 0)],
            'payment_method' => ['required', Rule::in($availablePaymentMethods)],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama lengkap wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp wajib diisi.',
            'customer_phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan awalan 08, 62, atau +62.',
            'customer_address.required' => 'Alamat wajib diisi.',
            'customer_address.min' => 'Alamat minimal 10 karakter.',
            'courier.required' => 'Pilih kurir pengiriman.',
            'courier.in' => 'Kurir yang dipilih tidak valid.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'quantity.max' => 'Jumlah melebihi stok yang tersedia.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran yang dipilih tidak tersedia.',
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah.',
            'payment_proof.file' => 'Bukti pembayaran harus berupa file.',
            'payment_proof.mimes' => 'Format harus JPG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran file maksimal 2MB.',
        ];
    }
}
