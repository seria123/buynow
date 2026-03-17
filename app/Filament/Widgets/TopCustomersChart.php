<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Sales\Order;
use Filament\Widgets\ChartWidget;

class TopCustomersChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Customers by Lifetime Value';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $topCustomers = User::query()
            ->with(['orders'])
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'lifetime_value' => $user->orders->sum('total_amount'),
                ];
            })
            ->sortByDesc('lifetime_value')
            ->take(10);

        $names = $topCustomers->pluck('name')->map(function ($name) {
            // Truncate long names
            return strlen($name) > 15 ? substr($name, 0, 12) . '...' : $name;
        })->toArray();

        $values = $topCustomers->pluck('lifetime_value')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Lifetime Value (KES)',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(6, 182, 212, 0.8)',
                        'rgba(132, 204, 22, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(168, 85, 247)',
                        'rgb(236, 72, 153)',
                        'rgb(20, 184, 166)',
                        'rgb(249, 115, 22)',
                        'rgb(139, 92, 246)',
                        'rgb(6, 182, 212)',
                        'rgb(132, 204, 22)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $names,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "KES " + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
