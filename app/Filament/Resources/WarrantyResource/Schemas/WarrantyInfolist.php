<?php

namespace App\Filament\Resources\WarrantyResource\Schemas;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;

class WarrantyInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Warranty Identification')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('warranty_number')
                                    ->label('Warranty Number')
                                    ->size('lg')
                                    ->fontFamily('mono')
                                    ->copyable(),

                                TextEntry::make('warranty_type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'standard' => 'info',
                                        'extended' => 'warning',
                                        'lifetime' => 'success',
                                        'manufacturer' => 'purple',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'standard' => 'Standard',
                                        'extended' => 'Extended',
                                        'lifetime' => 'Lifetime',
                                        'manufacturer' => 'Manufacturer',
                                        default => ucfirst($state),
                                    }),

                                TextEntry::make('status_label')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Active' => 'success',
                                        'Expired' => 'danger',
                                        'Claimed' => 'warning',
                                        'Cancelled' => 'gray',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Section::make('Product & Customer')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextEntry::make('product.name')
                                        ->label('Product')
                                        ->size('lg')
                                        ->weight('medium'),
                                    
                                    TextEntry::make('product.sku')
                                        ->label('SKU')
                                        ->fontFamily('mono')
                                        ->placeholder('N/A'),
                                ]),

                                Group::make([
                                    TextEntry::make('user.name')
                                        ->label('Customer')
                                        ->size('lg')
                                        ->weight('medium'),
                                    
                                    TextEntry::make('user.email')
                                        ->label('Email')
                                        ->placeholder('N/A'),
                                ]),
                            ]),
                    ]),

                Section::make('Warranty Period')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('start_date')
                                    ->label('Start Date')
                                    ->date('M j, Y'),

                                TextEntry::make('end_date')
                                    ->label('End Date')
                                    ->date('M j, Y'),

                                TextEntry::make('remaining_days')
                                    ->label('Remaining')
                                    ->formatStateUsing(fn ($record) => $record->remaining_days . ' days')
                                    ->color(fn ($record) => $record->remaining_days <= 30 ? 'danger' : ($record->remaining_days <= 90 ? 'warning' : 'success')),
                            ]),
                    ]),

                Section::make('Order Information')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('order.order_number')
                                    ->label('Order Number')
                                    ->placeholder('Not linked')
                                    ->fontFamily('mono'),

                                TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->placeholder('System'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->order || $record->created_by),

                Section::make('Terms & Instructions')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('terms')
                            ->label('Terms & Conditions')
                            ->prose()
                            ->placeholder('No terms specified'),

                        TextEntry::make('claim_instructions')
                            ->label('Claim Instructions')
                            ->prose()
                            ->placeholder('No instructions specified'),
                    ])
                    ->collapsible(),

                Section::make('Documentation')
                    ->icon('heroicon-o-document-arrow-down')
                    ->schema([
                        TextEntry::make('document_path')
                            ->label('Warranty Document')
                            ->placeholder('No document uploaded')
                            ->url(fn ($record) => $record->document_path ? asset('storage/' . $record->document_path) : null)
                            ->openUrlInNewTab(),
                    ])
                    ->visible(fn ($record) => $record->document_path),
            ]);
    }
}