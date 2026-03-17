<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Sales\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverviewWidget extends BaseWidget
{
    public ?string $filter = 'all';

    protected function getStats(): array
    {
        $totalCustomers = User::count();
        
        // Active customers (ordered in last 90 days)
        $ninetyDaysAgo = now()->subDays(90);
        $activeCustomers = User::whereHas('orders', function ($query) use ($ninetyDaysAgo) {
            $query->where('created_at', '>=', $ninetyDaysAgo);
        })->count();

        $inactiveCustomers = $totalCustomers - $activeCustomers;

        // Returning customers (more than 1 order)
        $returningCustomers = User::whereHas('orders')
            ->withCount('orders')
            ->having('orders_count', '>', 1)
            ->count();

        // New customers (only 1 order)
        $newCustomers = $totalCustomers - $returningCustomers;

        // Total lifetime value
        $totalLTV = Order::sum('total_amount');

        // Average CLV
        $averageCLV = $totalCustomers > 0 ? $totalLTV / $totalCustomers : 0;

        // Total orders
        $totalOrders = Order::count();

        // Average orders per customer
        $avgOrdersPerCustomer = $totalCustomers > 0 ? $totalOrders / $totalCustomers : 0;

        return [
            Stat::make('Total Customers', number_format($totalCustomers))
                ->description('All registered customers')
                ->icon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('Active Customers', number_format($activeCustomers))
                ->description(number_format($inactiveCustomers) . ' inactive')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
            
            Stat::make('Returning Customers', number_format($returningCustomers))
                ->description(number_format($newCustomers) . ' new')
                ->icon('heroicon-o-arrow-path')
                ->color('info'),
            
            Stat::make('Total Lifetime Value', 'KES ' . number_format($totalLTV, 2))
                ->description('KES ' . number_format($averageCLV, 2) . ' average')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning'),
            
            Stat::make('Total Orders', number_format($totalOrders))
                ->description(number_format($avgOrdersPerCustomer, 1) . ' avg per customer')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray'),
        ];
    }
}
