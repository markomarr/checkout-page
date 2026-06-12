<?php

namespace App\Filament\Brand\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BrandStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $brandId = auth()->user()->brand->id;

        $ordersThisMonth = Order::where('brand_id', $brandId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        $totalOrders = $ordersThisMonth->count();

        $pendingOrders = Order::where('brand_id', $brandId)
            ->where('status', 'pending')
            ->count();

        $revenue = (clone $ordersThisMonth)
            ->where('status', '!=', 'cancelled')
            ->sum('total_price');

        return [
            Stat::make('Order Bulan Ini', $totalOrders),
            Stat::make('Order Pending', $pendingOrders)
                ->description('Perlu tindakan')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($revenue, 0, ',', '.')),
        ];
    }
}
