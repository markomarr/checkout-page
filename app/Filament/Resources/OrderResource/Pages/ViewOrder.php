<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order')
                    ->schema([
                        TextEntry::make('order_code')->label('Kode Order'),
                        TextEntry::make('brand.name')->label('Brand'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->label('Tanggal')->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),

                Section::make('Produk')
                    ->schema([
                        TextEntry::make('product.name')->label('Produk'),
                        TextEntry::make('quantity')->label('Jumlah'),
                        TextEntry::make('total_price')->label('Total')->money('idr'),
                        TextEntry::make('courier')->label('Kurir'),
                        TextEntry::make('payment_method')->label('Metode Pembayaran'),
                    ])
                    ->columns(2),

                Section::make('Customer')
                    ->schema([
                        TextEntry::make('customer_name')->label('Nama'),
                        TextEntry::make('customer_phone')->label('Nomor WhatsApp'),
                        TextEntry::make('customer_address')->label('Alamat')->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Bukti Pembayaran')
                    ->schema([
                        ImageEntry::make('payment_proof_path')
                            ->label('')
                            ->disk('public')
                            ->height(400),
                    ]),
            ]);
    }
}
