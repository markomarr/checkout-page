<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Brand;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $brandData = [
            'name' => $data['brand_name'],
            'slug' => $data['brand_slug'],
            'whatsapp_number' => $data['brand_whatsapp_number'],
        ];

        unset($data['brand_name'], $data['brand_slug'], $data['brand_whatsapp_number']);

        $data['role'] = 'brand_owner';

        $user = User::create($data);

        $user->brand()->save(new Brand($brandData));

        return $user;
    }
}
