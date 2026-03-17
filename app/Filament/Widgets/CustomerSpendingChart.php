<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Sales\Order;
use Filament\Widgets\ChartWidget;

class CustomerSpendingChart extends ChartWidget
{
    protected ?string $heading = 'Customer Distribution by Spending';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get customers grouped by spending level
        $highSpenders = User::whereHas('orders')
            ->withCount('orders')
            ->get()
            ->filter(function ($user) {
                return $user->orders->sum('total_amount') >= 100000;
            })->count();

        $mediumSpenders = User::whereHas('orders')
            ->withCount('orders')
            ->get()
            ->filter(function ($user) {
                $total = $user->orders->sum('total_amount');
                return $total >= 50000 && $total < 100000;
            })->count();

        $lowSpenders = User::whereHas('orders')
            ->withCount('orders')
            ->get()
            ->filter(function ($user) {
                $total = $user->orders->sum('total_amount');
                return $total > 0 && $total < 50000;
            })->count();

        $noOrders = User::whereDoesntHave('orders')->count();

        return [
            'datasets' => [
                [
                    'data' => [$highSpenders, $mediumSpenders, $lowSpenders, $noOrders],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Green - High
                        'rgba(251, 191, 36, 0.8)',  // Yellow - Medium
                        'rgba(239, 68, 68, 0.8)',   // Red - Low
                        'rgba(156, 163, 175, 0.8)', // Gray - No orders
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                        'rgb(156, 163, 175)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => [
                'High Spenders (100k+)',
                'Medium Spenders (50k-100k)',
                'Low Spenders (<50k)',
                'No Orders',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
