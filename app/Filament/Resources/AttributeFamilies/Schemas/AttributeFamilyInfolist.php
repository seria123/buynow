<?php

namespace App\Filament\Resources\AttributeFamilies\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttributeFamilyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attribute Family Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Family Name')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-tag')
                            ->iconColor('primary'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->copyMessage('Slug copied!')
                            ->icon('heroicon-o-link'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),

                        TextEntry::make('attributes')
                            ->label('Attributes Count')
                            ->formatStateUsing(fn ($record) => $record?->attributes?->count() ?? 0)
                            ->icon('heroicon-o-list-bullet')
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2),

                Section::make('Status & Metadata')
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('sort_order')
                            ->label('Sort Order')
                            ->icon('heroicon-o-arrows-up-down')
                            ->badge()
                            ->color('gray'),

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
            ]);
    }
}
