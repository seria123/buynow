<?php

namespace App\Filament\Resources\PromotionResource\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;

class PromotionInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Promotion Details')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->size('lg'),
                        TextEntry::make('code')
                            ->label('Promo Code')
                            ->size('lg')
                            ->fontFamily('mono')
                            ->copyable(),
                    ])->columns(2),

                Section::make('Discount Information')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Discount Type')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success')
                                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Percentage (%)' : 'Fixed Amount'),

                                TextEntry::make('value')
                                    ->label('Discount Value')
                                    ->formatStateUsing(function ($state, $record) {
                                        if ($record->type === 'percentage') {
                                            return $state . '%';
                                        }
                                        return '$' . number_format($state, 2);
                                    }),

                                TextEntry::make('minimum_order_amount')
                                    ->label('Minimum Order')
                                    ->formatStateUsing(fn ($state) => $state ? '$' . number_format($state, 2) : 'No minimum'),
                            ]),
                    ]),

                Section::make('Usage & Validity')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('usage_limit')
                                        ->label('Usage Limit'),
                                    TextEntry::make('used_count')
                                        ->label('Times Used'),
                                ])->columns(2),

                                Group::make([
                                    TextEntry::make('starts_at')
                                        ->label('Start Date')
                                        ->date('M j, Y g:i A')
                                        ->placeholder('Immediate'),

                                    TextEntry::make('expires_at')
                                        ->label('End Date')
                                        ->date('M j, Y g:i A')
                                        ->placeholder('No expiration'),
                                ])->columns(2),
                            ]),
                    ]),

                Section::make('Status')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('status_label')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Active' => 'success',
                                'Expired' => 'danger',
                                'Upcoming' => 'warning',
                                'Limit Reached' => 'warning',
                                'Inactive' => 'gray',
                                default => 'gray',
                            }),
                    ]),
            ]);
    }
}
