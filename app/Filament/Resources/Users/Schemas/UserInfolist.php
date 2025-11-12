<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->description('High-level glance at who the user is and how to contact them.')
                    ->icon('heroicon-o-identification')
                    ->columns([
                        'sm' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('name')
                            ->label('User')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-user-circle')
                            ->helperText(fn ($record): ?string => $record->username ? sprintf('@%s', $record->username) : null),
                        TextEntry::make('email')
                            ->label('Email address')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                        TextEntry::make('username')
                            ->placeholder('—'),
                        TextEntry::make('roles.name')
                            ->label('Roles')
                            ->badge()
                            ->limitList(5)
                            ->placeholder('No roles assigned.')
                            ->columnSpanFull(),
                        ImageEntry::make('avatar')
                            ->label('Avatar')
                            ->getStateUsing(fn ($record): ?string => $record->getFirstMediaUrl('avatar'))
                            ->defaultImageUrl(fn ($record): string => sprintf(
                                'https://ui-avatars.com/api/?name=%s&background=FFEB3B&color=222&size=128&bold=true&length=2&rounded=true',
                                urlencode((string) ($record->name ?? 'User'))
                            ))
                            ->circular()
                            ->height(128)
                            ->width(128)
                            ->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->description('Security posture and lifecycle dates.')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('email_verified_at')
                            ->label('Email verified at')
                            ->badge()
                            ->formatStateUsing(fn (?Carbon $state): string => $state ? sprintf('Verified %s', $state->diffForHumans()) : 'Not verified')
                            ->color(fn (?Carbon $state): string => $state ? 'success' : 'warning')
                            ->placeholder('Not verified'),
                        TextEntry::make('two_factor_confirmed_at')
                            ->label('2FA confirmed at')
                            ->badge()
                            ->formatStateUsing(fn (?Carbon $state): string => $state ? sprintf('Enabled %s', $state->diffForHumans()) : 'Not enabled')
                            ->color(fn (?Carbon $state): string => $state ? 'success' : 'gray')
                            ->placeholder('Not enabled'),
                        TextEntry::make('created_at')
                            ->label('Joined')
                            ->since()
                            ->helperText(fn (?Carbon $state): ?string => $state ? $state->toFormattedDateString() : null)
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->since()
                            ->helperText(fn (?Carbon $state): ?string => $state ? $state->toDayDateTimeString() : null)
                            ->placeholder('-'),
                    ]),
                Section::make('Connected Accounts')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('google_id')
                            ->label('Google ID')
                            ->placeholder('—')
                            ->copyable()
                            ->columnSpanFull(),
                        TextEntry::make('facebook_id')
                            ->label('Facebook ID')
                            ->placeholder('—')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Two-factor Backups')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('two_factor_secret')
                            ->label('Two-factor secret')
                            ->placeholder('Not available')
                            ->columnSpanFull()
                            ->copyable(),
                        TextEntry::make('two_factor_recovery_codes')
                            ->label('Recovery codes')
                            ->placeholder('Not available')
                            ->columnSpanFull()
                            ->copyable(),
                    ]),
            ]);
    }
}
