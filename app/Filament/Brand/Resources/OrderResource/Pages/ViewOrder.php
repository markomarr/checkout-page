<?php

namespace App\Filament\Brand\Resources\OrderResource\Pages;

use App\Filament\Brand\Resources\OrderResource;
use App\Support\WhatsApp;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Order')
                    ->schema([
                        TextEntry::make('order_code')
                            ->label('Kode Order'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => OrderResource::STATUS_LABELS[$state] ?? $state)
                            ->color(fn (string $state): string|array => OrderResource::STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('created_at')
                            ->label('Tanggal')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(3),

                Section::make('Data Customer')
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Nama'),
                        TextEntry::make('customer_phone')
                            ->label('Nomor WhatsApp'),
                        TextEntry::make('customer_address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Produk & Pengiriman')
                    ->schema([
                        TextEntry::make('product.name')
                            ->label('Produk'),
                        TextEntry::make('quantity')
                            ->label('Jumlah'),
                        TextEntry::make('total_price')
                            ->label('Total')
                            ->money('idr'),
                        TextEntry::make('courier')
                            ->label('Kurir')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'jne' => 'JNE Reguler',
                                'jnt' => 'J&T Express',
                                'sicepat' => 'SiCepat BEST',
                                default => $state,
                            }),
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'bank_transfer' => 'Transfer Bank',
                                'qris' => 'QRIS',
                                default => $state,
                            }),
                    ])
                    ->columns(2),

                Section::make('Bukti Pembayaran')
                    ->schema([
                        ImageEntry::make('payment_proof_path')
                            ->label('')
                            ->disk('local')
                            ->visibility('private')
                            ->visible(fn ($record): bool => $record->payment_proof_path
                                && in_array(strtolower(pathinfo($record->payment_proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])),
                        TextEntry::make('payment_proof_path')
                            ->label('Bukti Pembayaran')
                            ->visible(fn ($record): bool => $record->payment_proof_path
                                && strtolower(pathinfo($record->payment_proof_path, PATHINFO_EXTENSION)) === 'pdf')
                            ->formatStateUsing(fn (): string => 'File PDF tersedia, gunakan tombol "Download Bukti Pembayaran" di atas.'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadProof')
                ->label('Download Bukti Pembayaran')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn () => filled($this->record->payment_proof_path))
                ->url(fn () => Storage::disk('local')->temporaryUrl(
                    $this->record->payment_proof_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),

            Actions\Action::make('confirm')
                ->label('Konfirmasi Pembayaran')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending')
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);

                    $message = "Halo {$this->record->customer_name}, pesanan Anda dengan kode {$this->record->order_code} sudah kami konfirmasi dan sedang diproses. Terima kasih!";

                    Notification::make()
                        ->title('Order dikonfirmasi')
                        ->success()
                        ->actions([
                            NotificationAction::make('whatsapp')
                                ->label('Kirim notifikasi WhatsApp ke customer')
                                ->url(WhatsApp::link($this->record->customer_phone, $message), shouldOpenInNewTab: true)
                                ->button(),
                        ])
                        ->persistent()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('process')
                ->label('Proses Pesanan')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'confirmed')
                ->action(function () {
                    $this->record->update(['status' => 'processing']);
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('ship')
                ->label('Tandai Dikirim')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'processing')
                ->action(function () {
                    $this->record->update(['status' => 'shipped']);
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('done')
                ->label('Selesai')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'shipped')
                ->action(function () {
                    $this->record->update(['status' => 'done']);
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),

            Actions\Action::make('cancel')
                ->label('Batalkan')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => ! in_array($this->record->status, ['done', 'cancelled']))
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    $this->redirect(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
