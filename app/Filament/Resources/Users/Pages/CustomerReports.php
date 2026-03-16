<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\CustomerGroup;
use App\Models\User;
use App\Models\Sales\Order;
use BackedEnum;
use UnitEnum;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerReports extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 13;

    protected static ?string $slug = 'reports';

    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $customers = User::query()
            ->with(['orders', 'customerGroup']);

        // Total customers
        $totalCustomers = $customers->count();

        // Active customers (ordered in last 90 days)
        $ninetyDaysAgo = now()->subDays(90);
        $activeCustomers = User::whereHas('orders', function ($query) use ($ninetyDaysAgo) {
            $query->where('created_at', '>=', $ninetyDaysAgo);
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
        ];
    }

   public function table(Table $table): Table
   {
       return $table
           ->query(User::query()->with(['orders', 'customerGroup'])
               ->selectRaw('users.*, (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE orders.user_id = users.id) as lifetime_value')
               ->selectRaw('(SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) as total_orders')
               ->selectRaw('(SELECT MIN(created_at) FROM orders WHERE orders.user_id = users.id) as first_order_date')
               ->selectRaw('(SELECT MAX(created_at) FROM orders WHERE orders.user_id = users.id) as last_order_date')
           )
           ->columns([
            TextColumn::make('name')
                ->label('Customer')
                ->searchable()
                ->sortable(),
            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->sortable(),
            TextColumn::make('customerGroup.name')
                ->label('Group')
                ->badge()
                ->color('success'),
            TextColumn::make('orders_count')
                ->label('Orders')
                ->counts('orders')
                ->sortable(),
            TextColumn::make('lifetime_value')
                ->label('Lifetime Value')
                ->money('KES')
                ->sortable(),
            TextColumn::make('average_order_value')
                ->label('Avg Order')
                ->money('KES')
                ->sortable(),
            TextColumn::make('first_order_date')
                ->label('First Order')
                ->date('M j, Y')
                ->sortable(),
            TextColumn::make('last_order_date')
                ->label('Last Order')
                ->date('M j, Y')
                ->sortable(),
            TextColumn::make('is_active')
                ->label('Status')
                ->badge()
                ->color(fn (bool $state) => $state ? 'success' : 'gray')
                ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive'),
        ])
        ->filters([
            SelectFilter::make('customer_group_id')
                ->label('Customer Group')
                ->options(fn () => CustomerGroup::pluck('name', 'id'))
                ->multiple(),

            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ]),

            SelectFilter::make('customer_type')
                ->label('Customer Type')
                ->options([
                    'returning' => 'Returning',
                    'new' => 'New',
                ]),

            // Date range filter using Filter::form()
            Filter::make('registered')
                ->form([
                    DatePicker::make('registered_from')
                        ->label('Registered From'),
                    DatePicker::make('registered_until')
                        ->label('Registered Until'),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['registered_from'])) {
                        $query->whereDate('created_at', '>=', $data['registered_from']);
                    }
                    if (!empty($data['registered_until'])) {
                        $query->whereDate('created_at', '<=', $data['registered_until']);
                    }
                }),
        ])
        ->filtersFormColumns(4)
        ->actions([
            Action::make('view_orders')
                ->label('View Orders')
                ->url(fn (User $record) => route('filament.admin.resources.users.view', $record))
                ->openUrlInNewTab(),
        ])
        ->headerActions([
            ExportAction::make()
                ->exporter(\App\Filament\Exporters\CustomerExporter::class)
                ->formats([
                    ExportFormat::Csv,
                    ExportFormat::Xlsx,
                ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray'),
        ])
        ->defaultSort('lifetime_value', 'desc')
        ->paginated([10, 25, 50, 100]);
   }
}
