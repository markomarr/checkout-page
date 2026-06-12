<?php

namespace App\Filament\Brand\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CheckoutLinks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Link Checkout';

    protected static ?string $title = 'Link Checkout';

    protected static string $view = 'filament.brand.pages.checkout-links';

    public function getProducts(): Collection
    {
        $brand = auth()->user()->brand;

        return $brand->products()
            ->active()
            ->get()
            ->map(fn ($product) => [
                'name' => $product->name,
                'url' => url("/{$brand->slug}/{$product->slug}"),
            ]);
    }
}
