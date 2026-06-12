<?php

namespace App\Filament\Brand\Pages;

use App\Support\WhatsApp;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Setting Brand';

    protected static ?string $title = 'Setting Brand';

    protected static string $view = 'filament.brand.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $brand = auth()->user()->brand;

        $this->form->fill([
            'logo_path' => $brand->logo_path,
            'whatsapp_number' => $brand->whatsapp_number,
            'bank_name' => $brand->bank_name,
            'bank_account_number' => $brand->bank_account_number,
            'bank_account_name' => $brand->bank_account_name,
            'qris_image_path' => $brand->qris_image_path,
        ]);
    }

    public function form(Form $form): Form
    {
        $brandId = auth()->user()->brand->id;

        return $form
            ->schema([
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo Brand')
                    ->image()
                    ->maxSize(2048)
                    ->directory("logos/{$brandId}")
                    ->visibility('public'),
                Forms\Components\TextInput::make('whatsapp_number')
                    ->label('Nomor WhatsApp')
                    ->required()
                    ->maxLength(20)
                    ->helperText('Format 08xx atau +62xx akan otomatis dinormalisasi.'),
                Forms\Components\TextInput::make('bank_name')
                    ->label('Nama Bank')
                    ->maxLength(100),
                Forms\Components\TextInput::make('bank_account_number')
                    ->label('Nomor Rekening')
                    ->maxLength(50),
                Forms\Components\TextInput::make('bank_account_name')
                    ->label('Nama Pemilik Rekening')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('qris_image_path')
                    ->label('Gambar QRIS')
                    ->image()
                    ->maxSize(2048)
                    ->directory("qris/{$brandId}")
                    ->visibility('public'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data['whatsapp_number'] = WhatsApp::normalize($data['whatsapp_number']);

        auth()->user()->brand->update($data);

        Notification::make()
            ->title('Setting brand berhasil disimpan')
            ->success()
            ->send();
    }

    public function getPaymentNotReadyProperty(): bool
    {
        $brand = auth()->user()->brand;

        return blank($brand->bank_account_number) && blank($brand->qris_image_path);
    }
}
