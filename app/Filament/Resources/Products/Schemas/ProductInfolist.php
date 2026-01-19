<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Product Name')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-cube')
                            ->iconColor('primary'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->copyMessage('Slug copied!')
                            ->icon('heroicon-o-link'),

                        TextEntry::make('sku')
                            ->label('SKU')
                            ->copyable()
                            ->copyMessage('SKU copied!')
                            ->icon('heroicon-o-tag')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('category.name')
                            ->label('Category')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-folder')
                            ->placeholder('No category'),

                        TextEntry::make('brand.name')
                            ->label('Brand')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-building-storefront')
                            ->placeholder('No brand'),

                        TextEntry::make('attributeFamily.name')
                            ->label('Attribute Family')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-list-bullet')
                            ->placeholder('No attribute family'),

                        TextEntry::make('short_description')
                            ->label('Short Description')
                            ->columnSpanFull()
                            ->placeholder('No short description'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->html()
                            ->placeholder('No description'),
                    ])
                    ->columns(2),

                Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('thumbnail')
                            ->label('Thumbnail')
                            ->collection('thumbnail')
                            ->conversion('thumb')
                            ->height(200)
                            ->width(200)
                            ->columnSpanFull()
                            ->placeholder('No thumbnail uploaded'),

                        SpatieMediaLibraryImageEntry::make('images')
                            ->label('Product Images')
                            ->collection('images')
                            ->height(150)
                            ->columnSpanFull()
                            ->placeholder('No images uploaded'),
                    ]),

                Section::make('Pricing & Stock')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Price')
                            ->money('KES')
                            ->icon('heroicon-o-currency-dollar')
                            ->iconColor('success'),

                        TextEntry::make('compare_price')
                            ->label('Compare Price')
                            ->money('KES')
                            ->icon('heroicon-o-tag')
                            ->placeholder('—'),

                        TextEntry::make('cost')
                            ->label('Cost')
                            ->money('KES')
                            ->icon('heroicon-o-banknotes')
                            ->placeholder('—'),

                        TextEntry::make('quantity')
                            ->label('Quantity')
                            ->badge()
                            ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                            ->icon('heroicon-o-archive-box'),

                        TextEntry::make('low_stock_threshold')
                            ->label('Low Stock Threshold')
                            ->badge()
                            ->color('warning')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                Section::make('Status & Metadata')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Review Status')
                            ->badge()
                            ->color(fn ($state) => $state->color())
                            ->icon(fn ($state) => $state->icon())
                            ->formatStateUsing(fn ($state) => $state->label()),

                        IconEntry::make('published')
                            ->label('Published')
                            ->boolean()
                            ->trueIcon('heroicon-o-eye')
                            ->falseIcon('heroicon-o-eye-slash')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        IconEntry::make('is_featured')
                            ->label('Featured')
                            ->boolean()
                            ->trueIcon('heroicon-o-star')
                            ->falseIcon('heroicon-o-star')
                            ->trueColor('warning')
                            ->falseColor('gray'),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->icon('heroicon-o-user')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Unknown'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-calendar')
                            ->iconColor('info'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-clock')
                            ->iconColor('warning'),
                    ])
                    ->columns(3),

                Section::make('Review Information')
                    ->schema([
                        TextEntry::make('reviewer.name')
                            ->label('Reviewed By')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-check-circle')
                            ->placeholder('Not reviewed yet')
                            ->visible(fn ($record) => filled($record->reviewer_id)),

                        TextEntry::make('reviewed_at')
                            ->label('Reviewed At')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-o-calendar')
                            ->iconColor('info')
                            ->placeholder('Not reviewed yet')
                            ->visible(fn ($record) => filled($record->reviewed_at)),

                        TextEntry::make('review_notes')
                            ->label('Review Notes')
                            ->columnSpanFull()
                            ->placeholder('No review notes provided')
                            ->visible(fn ($record) => filled($record->review_notes)),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => filled($record->reviewer_id) || filled($record->reviewed_at) || filled($record->review_notes)),
            ]);
    }
}
