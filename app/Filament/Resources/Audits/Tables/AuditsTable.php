<?php

namespace App\Filament\Resources\Audits\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->size('sm'),

                TextColumn::make('event')
                    ->label('Event')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'created' => 'heroicon-o-plus-circle',
                        'updated' => 'heroicon-o-pencil-square',
                        'deleted' => 'heroicon-o-trash',
                        'restored' => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-document',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        'restored' => 'warning',
                        default => 'gray',
                    })
                    ->size('sm'),

                TextColumn::make('auditable_type')
                    ->label('Model')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->description(fn ($record): ?string => $record->auditable_id ? "ID: {$record->auditable_id}" : null)
                    ->wrap()
                    ->size('sm'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->default('System')
                    ->icon('heroicon-o-user')
                    ->description(fn ($record): ?string => $record->user_type ? class_basename($record->user_type) : null)
                    ->wrap()
                    ->size('sm'),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-globe-alt')
                    ->copyable()
                    ->copyMessage('IP address copied')
                    ->toggleable()
                    ->size('sm'),

                TextColumn::make('url')
                    ->label('URL')
                    ->sortable()
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->url)
                    ->copyable()
                    ->copyMessage('URL copied')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap()
                    ->size('sm'),

                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->description(fn ($record): string => $record->created_at->diffForHumans())
                    ->size('sm'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event Type')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                    ])
                    ->multiple(),

                SelectFilter::make('auditable_type')
                    ->label('Model Type')
                    ->options(function () {
                        return \OwenIt\Auditing\Models\Audit::query()
                            ->distinct()
                            ->pluck('auditable_type', 'auditable_type')
                            ->map(fn ($type) => class_basename($type))
                            ->toArray();
                    })
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('User')
                    ->options(function () {
                        return \App\Models\User::query()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('date_range')
                    ->label('Date Range')
                    ->options([
                        'last_6_hours' => 'Last 6 Hours',
                        'last_12_hours' => 'Last 12 Hours',
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'last_7_days' => 'Last 7 Days',
                        'last_30_days' => 'Last 30 Days',
                        'this_week' => 'This Week',
                        'last_week' => 'Last Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'last_6_hours' => $query->where('created_at', '>=', Carbon::now()->subHours(6)),
                            'last_12_hours' => $query->where('created_at', '>=', Carbon::now()->subHours(12)),
                            'today' => $query->whereDate('created_at', Carbon::today()),
                            'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
                            'last_7_days' => $query->where('created_at', '>=', Carbon::now()->subDays(7)),
                            'last_30_days' => $query->where('created_at', '>=', Carbon::now()->subDays(30)),
                            'this_week' => $query->whereBetween('created_at', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek(),
                            ]),
                            'last_week' => $query->whereBetween('created_at', [
                                Carbon::now()->subWeek()->startOfWeek(),
                                Carbon::now()->subWeek()->endOfWeek(),
                            ]),
                            'this_month' => $query->whereMonth('created_at', Carbon::now()->month)
                                ->whereYear('created_at', Carbon::now()->year),
                            'last_month' => $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                                ->whereYear('created_at', Carbon::now()->subMonth()->year),
                            'this_year' => $query->whereYear('created_at', Carbon::now()->year),
                            default => $query,
                        };
                    })
                    ->default('today'),

                Filter::make('created_at')
                    ->label('Custom Date Range')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From '.\Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until '.\Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s');
    }
}
