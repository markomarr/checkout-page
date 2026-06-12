<x-filament-panels::page>
    @if ($this->paymentNotReady)
        <div class="rounded-lg bg-warning-50 p-4 text-sm text-warning-700 ring-1 ring-warning-200 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30">
            Checkout page belum bisa aktif. Isi minimal satu metode pembayaran.
        </div>
    @endif

    <form wire:submit="save">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Simpan
        </x-filament::button>
    </form>
</x-filament-panels::page>
