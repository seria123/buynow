<?php

namespace App\Filament\Resources\Audits\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_type'),
                TextInput::make('user_id'),
                TextInput::make('event')
                    ->required(),
                TextInput::make('auditable_type')
                    ->required(),
                TextInput::make('auditable_id')
                    ->required(),
                Textarea::make('old_values')
                    ->columnSpanFull(),
                Textarea::make('new_values')
                    ->columnSpanFull(),
                Textarea::make('url')
                    ->columnSpanFull(),
                TextInput::make('ip_address'),
                TextInput::make('user_agent'),
                TextInput::make('tags'),
            ]);
    }
}
