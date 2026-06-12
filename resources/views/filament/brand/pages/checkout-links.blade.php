<x-filament-panels::page>
    @php($products = $this->getProducts())

    @if ($products->isEmpty())
        <div class="rounded-lg bg-gray-50 p-6 text-center text-sm text-gray-500 ring-1 ring-gray-200 dark:bg-white/5 dark:text-gray-400 dark:ring-white/10">
            Belum ada produk aktif. Tambahkan produk di menu Produk.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($products as $product)
                <div
                    x-data="{ copied: false }"
                    class="flex items-center justify-between gap-4 rounded-lg bg-white p-4 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-white/10"
                >
                    <div class="min-w-0">
                        <p class="font-medium text-gray-950 dark:text-white">{{ $product['name'] }}</p>
                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $product['url'] }}</p>
                    </div>

                    <x-filament::button
                        type="button"
                        color="gray"
                        x-on:click="
                            navigator.clipboard.writeText('{{ $product['url'] }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                    >
                        <span x-show="!copied">Salin link</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                    </x-filament::button>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
