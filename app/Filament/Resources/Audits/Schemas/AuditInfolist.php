<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Audit ID'),

                                TextEntry::make('event')
                                    ->label('Event')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'created' => 'success',
                                        'updated' => 'info',
                                        'deleted' => 'danger',
                                        'restored' => 'warning',
                                        default => 'gray',
                                    }),

                                TextEntry::make('auditable_type')
                                    ->label('Model Type')
                                    ->formatStateUsing(fn (string $state): string => class_basename($state)),

                                TextEntry::make('auditable_id')
                                    ->label('Model ID'),

                                TextEntry::make('created_at')
                                    ->label('Date & Time')
                                    ->dateTime(),
                            ]),
                    ])
                    ->columns(1),

                Section::make('User Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->default('System'),

                                TextEntry::make('user_type')
                                    ->label('User Type')
                                    ->default('N/A'),

                                TextEntry::make('ip_address')
                                    ->label('IP Address')
                                    ->default('N/A'),

                                TextEntry::make('user_agent')
                                    ->label('User Agent')
                                    ->default('N/A')
                                    ->columnSpanFull(),

                                TextEntry::make('url')
                                    ->label('URL')
                                    ->default('N/A')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Old Values')
                    ->schema([
                        KeyValueEntry::make('old_values')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('Old Value')
                            ->default([]),
                    ])
                    ->visible(fn ($record) => ! empty($record->old_values))
                    ->collapsible(),

                Section::make('New Values')
                    ->schema([
                        KeyValueEntry::make('new_values')
                            ->label('')
                            ->keyLabel('Field')
                            ->valueLabel('New Value')
                            ->default([]),
                    ])
                    ->visible(fn ($record) => ! empty($record->new_values))
                    ->collapsible(),

                Section::make('Tags')
                    ->schema([
                        TextEntry::make('tags')
                            ->label('')
                            ->default('No tags'),
                    ])
                    ->visible(fn ($record) => ! empty($record->tags))
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
