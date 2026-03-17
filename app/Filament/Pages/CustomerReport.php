<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\CustomerGroup;
use App\Models\User;
use App\Models\Sales\Order;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerReport extends ListRecords
{
    use HasFiltersForm;

    protected static string $resource = UserResource::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Customer Reports';

    protected static ?string $title = 'Customer Analytics';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 13;


    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $query = $this->buildFilteredQuery();
        
        $totalCustomers = $query->count();

        // Active customers (ordered in last 90 days)
        $ninetyDaysAgo = now()->subDays(90);
        $activeCustomers = User::whereHas('orders', function ($q) use ($ninetyDaysAgo) {
            $q->where('created_at', '>=', $ninetyDaysAgo);
        })->count();

        // Inactive customers
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

        // Average orders per customer
        $totalOrders = Order::count();
        $avgOrdersPerCustomer = $totalCustomers > 0 ? $totalOrders / $totalCustomers : 0;

        // Customer growth (new customers this month vs last month)
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        
        $thisMonthNewCustomers = User::whereBetween('created_at', [$thisMonth, now()])->count();
        $lastMonthNewCustomers = User::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();
        
        $customerGrowth = $lastMonthNewCustomers > 0 
            ? (($thisMonthNewCustomers - $lastMonthNewCustomers) / $lastMonthNewCustomers) * 100 
            : 0;

        $this->stats = [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $inactiveCustomers,
            'returning_customers' => $returningCustomers,
            'new_customers' => $newCustomers,
            'total_ltv' => $totalLTV,
            'average_clv' => $averageCLV,
            'total_orders' => $totalOrders,
            'avg_orders_per_customer' => $avgOrdersPerCustomer,
            'this_month_new_customers' => $thisMonthNewCustomers,
            'customer_growth' => $customerGrowth,
        ];
    }

    protected function buildFilteredQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query()->with(['orders', 'customerGroup']);
        
        // Apply any table filters
        if (!empty($this->filters)) {
            if (isset($this->filters['customer_group_id'])) {
                $groups = $this->filters['customer_group_id'];
                if (is_array($groups) && !empty($groups)) {
                    $query->whereIn('customer_group_id', $groups);
                }
            }
            
            if (isset($this->filters['registered'])) {
                $registered = $this->filters['registered'];
                if (!empty($registered['registered_from'])) {
                    $query->whereDate('created_at', '>=', $registered['registered_from']);
                }
                if (!empty($registered['registered_until'])) {
                    $query->whereDate('created_at', '<=', $registered['registered_until']);
                }
            }
        }
        
        return $query;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    protected function getStatsWidgets(): array
    {
        return [
            \App\Filament\Widgets\CustomerStatsOverviewWidget::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CustomerStatsOverviewWidget::class,
            \App\Filament\Widgets\CustomerGrowthChart::class,
            \App\Filament\Widgets\CustomerSpendingChart::class,
            \App\Filament\Widgets\TopCustomersChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_stats')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('loadStats'),
            ExportAction::make()
                ->exporter(\App\Filament\Exporters\CustomerExporter::class)
                ->formats([
                    ExportFormat::Csv,
                    ExportFormat::Xlsx,
                ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->with(['orders', 'customerGroup'])
                    ->select('users.*')
                    ->selectSub(
                        Order::selectRaw('COALESCE(SUM(total_amount), 0)')
                            ->whereColumn('orders.user_id', 'users.id'),
                        'lifetime_value'
                    )
                    ->selectSub(
                        Order::selectRaw('COUNT(*)')
                            ->whereColumn('orders.user_id', 'users.id'),
                        'total_orders'
                    )
                    ->selectSub(
                        Order::selectRaw('COALESCE(AVG(total_amount), 0)')
                            ->whereColumn('orders.user_id', 'users.id'),
                        'average_order_value'
                    )
                    ->selectSub(
                        Order::selectRaw('MIN(created_at)')
                            ->whereColumn('orders.user_id', 'users.id'),
                        'first_order_date'
                    )
                    ->selectSub(
                        Order::selectRaw('MAX(created_at)')
                            ->whereColumn('orders.user_id', 'users.id'),
                        'last_order_date'
                    )
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->phone ?? 'No phone'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope'),
                TextColumn::make('customerGroup.name')
                    ->label('Group')
                    ->badge()
                    ->color('success')
                    ->default('Default'),
                TextColumn::make('total_orders')
                    ->label('Orders')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 10 => 'success',
                        $state >= 5 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('lifetime_value')
                    ->label('Lifetime Value')
                    ->money('KES')
                    ->sortable()
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 100000 => 'success',
                        $state >= 50000 => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('average_order_value')
                    ->label('Avg Order')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('first_order_date')
                    ->label('First Order')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive'),
            ])
            ->filters([
                SelectFilter::make('customer_group_id')
                    ->label('Customer Group')
                    ->options(fn () => CustomerGroup::pluck('name', 'id')->prepend('Default', 'null'))
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'active') {
                            $query->whereHas('orders', function ($q) {
                                $q->where('created_at', '>=', now()->subDays(90));
                            });
                        } elseif ($data['value'] === 'inactive') {
                            $query->whereDoesntHave('orders', function ($q) {
                                $q->where('created_at', '>=', now()->subDays(90));
                            })->orWhereDoesntHave('orders');
                        }
                    }),

                SelectFilter::make('customer_type')
                    ->label('Customer Type')
                    ->options([
                        'returning' => 'Returning (2+ orders)',
                        'new' => 'New (1 order)',
                        'no_orders' => 'No Orders',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'returning') {
                            $query->whereHas('orders')
                                ->withCount('orders')
                                ->having('orders_count', '>', 1);
                        } elseif ($data['value'] === 'new') {
                            $query->whereHas('orders')
                                ->withCount('orders')
                                ->having('orders_count', '=', 1);
                        } elseif ($data['value'] === 'no_orders') {
                            $query->whereDoesntHave('orders');
                        }
                    }),

                SelectFilter::make('spending_level')
                    ->label('Spending Level')
                    ->options([
                        'high' => 'High (100k+)',
                        'medium' => 'Medium (50k-100k)',
                        'low' => 'Low (<50k)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'high') {
                            $query->whereHas('orders', function ($q) {
                                $q->selectRaw('SUM(total_amount) as total')
                                    ->groupBy('user_id')
                                    ->having('total', '>=', 100000);
                            });
                        } elseif ($data['value'] === 'medium') {
                            $query->whereHas('orders', function ($q) {
                                $q->selectRaw('SUM(total_amount) as total')
                                    ->groupBy('user_id')
                                    ->having('total', '>=', 50000)
                                    ->having('total', '<', 100000);
                            });
                        } elseif ($data['value'] === 'low') {
                            $query->whereHas('orders', function ($q) {
                                $q->selectRaw('SUM(total_amount) as total')
                                    ->groupBy('user_id')
                                    ->having('total', '<', 50000);
                            });
                        }
                    }),

                Filter::make('registered')
                    ->label('Registration Date')
                    ->form([
                        DatePicker::make('registered_from')
                            ->label('From'),
                        DatePicker::make('registered_until')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['registered_from'])) {
                            $query->whereDate('created_at', '>=', $data['registered_from']);
                        }
                        if (!empty($data['registered_until'])) {
                            $query->whereDate('created_at', '<=', $data['registered_until']);
                        }
                    }),

                Filter::make('order_date')
                    ->label('Order Date')
                    ->form([
                        DatePicker::make('order_from')
                            ->label('From'),
                        DatePicker::make('order_until')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['order_from'])) {
                            $query->whereHas('orders', function ($q) use ($data) {
                                $q->whereDate('created_at', '>=', $data['order_from']);
                            });
                        }
                        if (!empty($data['order_until'])) {
                            $query->whereHas('orders', function ($q) use ($data) {
                                $q->whereDate('created_at', '<=', $data['order_until']);
                            });
                        }
                    }),
            ])
            ->filtersFormColumns(4)
            ->filtersFormWidth('full')
            ->actions([
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn (User $record) => route('filament.admin.resources.orders.index', ['tableFilters[user][value]' => $record->id])),
                Action::make('view_profile')
                    ->label('View Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn (User $record) => route('filament.admin.resources.users.view', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->exporter(\App\Filament\Exporters\CustomerExporter::class),
                    Tables\Actions\BulkAction::make('send_promotion')
                        ->label('Send Promotion')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            // Handle bulk promotion sending
                        }),
                ]),
            ])
            ->defaultSort('lifetime_value', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
