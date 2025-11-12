<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Permission name')
                            ->autofocus()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Use consistent naming such as `posts.create`.'),
                        TextInput::make('guard_name')
                            ->default('web')
                            ->readOnly()
                            ->helperText('Defaults to the `web` guard used by the application.'),
                    ]),
            ]);
    }
}
