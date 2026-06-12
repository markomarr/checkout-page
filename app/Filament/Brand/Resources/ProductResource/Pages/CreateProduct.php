<?php

namespace App\Filament\Brand\Resources\ProductResource\Pages;

use App\Filament\Brand\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['brand_id'] = auth()->user()->brand->id;

        return $data;
    }
}
