<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Summary')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Role')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('guard_name')
                            ->label('Guard')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('permissions.name')
                            ->label('Permissions')
                            ->badge()
                            ->limitList(8)
                            ->placeholder('No permissions attached yet.')
                            ->columnSpanFull(),
                        TextEntry::make('users_count')
                            ->counts('users')
                            ->label('Users with this role')
                            ->badge()
                            ->color('primary'),
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
