<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (User $record, Actions\DeleteAction $action): void {
                    if ($record->brand && $record->brand->orders()->exists()) {
                        Notification::make()
                            ->title('Tidak bisa menghapus')
                            ->body('Brand owner ini memiliki order aktif dan tidak bisa dihapus. Nonaktifkan saja.')
                            ->danger()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $brand = $this->record->brand;

        $data['brand_name'] = $brand?->name;
        $data['brand_slug'] = $brand?->slug;
        $data['brand_whatsapp_number'] = $brand?->whatsapp_number;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $brandData = [
            'name' => $data['brand_name'],
            'slug' => $data['brand_slug'],
            'whatsapp_number' => $data['brand_whatsapp_number'],
        ];

        unset($data['brand_name'], $data['brand_slug'], $data['brand_whatsapp_number']);

        $record->update($data);
        $record->brand?->update($brandData);

        return $record;
    }
}
