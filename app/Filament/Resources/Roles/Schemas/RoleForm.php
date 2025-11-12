<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Overview')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Role name')
                            ->autofocus()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('guard_name')
                            ->default('web')
                            ->readOnly()
                            ->helperText('Guard is fixed to the application default.'),
                    ]),
            ]);
    }
}
