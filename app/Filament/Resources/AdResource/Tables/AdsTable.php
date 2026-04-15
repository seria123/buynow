<?php

namespace App\Filament\Resources\AdResource\Tables;

use App\Models\Ad;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdsTable
{
    public static function getColumns(): array
    {
        return [
            ImageColumn::make('image')
                ->label('Image')
                ->size(60)
                ->square()
                ->disk('public'),

            TextColumn::make('title')
                ->label('Title')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->limit(30),

            TextColumn::make('position')
                ->label('Position')
                ->badge()
                ->color('primary'),

            TextColumn::make('type')
                ->label('Type')
                ->badge()
                ->color('success'),

            TextColumn::make('sort_order')
                ->label('Order')
                ->sortable()
                ->badge(),

            TextColumn::make('clicks')
                ->label('Clicks')
                ->sortable()
                ->badge(),

            TextColumn::make('impressions')
                ->label('Impressions')
                ->sortable()
                ->badge(),

            IconColumn::make('active')
                ->label('Active')
                ->boolean()
                ->sortable(),

            TextColumn::make('start_date')
                ->label('Start Date')
                ->dateTime('M j, Y g:i A')
                ->sortable()
                ->placeholder('Not set'),

            TextColumn::make('end_date')
                ->label('End Date')
                ->dateTime('M j, Y g:i A')
                ->sortable()
                ->placeholder('Not set'),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('position')
                ->label('Position')
                ->options([
                    'home' => 'Home Page',
                    'category' => 'Category Page',
                    'product' => 'Product Page',
                    'cart' => 'Cart Page',
                    'banner' => 'Banner Zone',
                ]),

            SelectFilter::make('type')
                ->label('Type')
                ->options([
                    'banner' => 'Banner Ad',
                    'promotion' => 'Promotion',
                    'featured' => 'Featured Product',
                    'flash_sale' => 'Flash Sale',
                    'category' => 'Category Link',
                ]),

            SelectFilter::make('active')
                ->label('Status')
                ->options([
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]),

            Filter::make('valid')
                ->label('Currently Valid')
                ->query(fn (Builder $query) => $query->where(function ($query) {
                    $query->where(function ($query) {
                        $query->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })->where(function ($query) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
                })),
        ];
    }

    public static function getActions(): array
    {
        return [
            ActionGroup::make([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                BulkAction::make('activate')
                    ->label('Activate')
                    ->action(fn ($records) => $records->each(fn ($record) => $record->update(['active' => true])))
                    ->requiresConfirmation()
                    ->color('success'),

                BulkAction::make('deactivate')
                    ->label('Deactivate')
                    ->action(fn ($records) => $records->each(fn ($record) => $record->update(['active' => false])))
                    ->requiresConfirmation()
                    ->color('danger'),

                BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->action(fn ($records) => $records->each(fn ($record) => $record->delete()))
                    ->requiresConfirmation()
                    ->color('danger'),
            ]),
        ];
    }
}
