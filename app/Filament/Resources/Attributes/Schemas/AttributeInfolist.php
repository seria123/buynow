<?php

namespace App\Filament\Resources\Attributes\Schemas;

use App\Models\Catalogue\AttributeType;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttributeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attribute Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Attribute Name')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-list-bullet')
                            ->iconColor('primary'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->copyMessage('Slug copied!')
                            ->icon('heroicon-o-link'),

                        TextEntry::make('attributeFamily.name')
                            ->label('Attribute Family')
                            ->icon('heroicon-o-tag')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('type')
                            ->label('Type')
                            ->formatStateUsing(function ($state) {
                                if ($state instanceof AttributeType) {
                                    return $state->label();
                                }

                                return $state === 'predefined' || $state === AttributeType::Predefined->value ? 'Predefined' : 'Manual';
                            })
                            ->badge()
                            ->color(function ($state) {
                                $isPredefined = $state instanceof AttributeType
                                    ? $state === AttributeType::Predefined
                                    : ($state === 'predefined' || $state === AttributeType::Predefined->value);

                                return $isPredefined ? 'success' : 'warning';
                            })
                            ->icon(function ($state) {
                                $isPredefined = $state instanceof AttributeType
                                    ? $state === AttributeType::Predefined
                                    : ($state === 'predefined' || $state === AttributeType::Predefined->value);

                                return $isPredefined ? 'heroicon-o-check-circle' : 'heroicon-o-pencil';
                            }),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description provided'),

                        TextEntry::make('attributeValues')
                            ->label('Values Count')
                            ->formatStateUsing(fn ($record) => $record?->attributeValues?->count() ?? 0)
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color('info')
                            ->visible(function ($record) {
                                $type = $record?->type;
                                if ($type instanceof AttributeType) {
                                    return $type === AttributeType::Predefined;
                                }

                                return $type === 'predefined' || $type === AttributeType::Predefined->value;
                            }),
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
