<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Summary')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Permission')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('guard_name')
                            ->label('Guard')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('roles.name')
                            ->label('Attached roles')
                            ->badge()
                            ->limitList(6)
                            ->placeholder('No roles attached yet.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Meta')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
