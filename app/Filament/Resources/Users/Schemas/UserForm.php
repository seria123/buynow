<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\CustomerGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile')
                    ->description('Core information shown across the application.')
                    ->icon('heroicon-o-identification')
                    ->columns([
                        'sm' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Full name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Jane Doe')
                            ->helperText('Visible in navigation, audit logs, and email signatures.'),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('jane@example.com')
                            ->autocomplete('email')
                            ->helperText('We send transactional messages here.'),
                        TextInput::make('username')
                            ->prefix('@')
                            ->maxLength(255)
                            ->placeholder('janedoe')
                            ->helperText('Optional handle used in URLs and mentions.')
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 1,
                            ]),
                        TextInput::make('avatar')
                            ->label('Avatar URL')
                            ->placeholder('https://...')
                            ->columnSpanFull(),
                    ]),
                Section::make('Customer Group')
                    ->description('Assign the user to a customer group for pricing and promotions.')
                    ->icon('heroicon-o-user-group')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Select::make('customer_group_id')
                            ->label('Customer Group')
                            ->placeholder('Select a customer group')
                            ->options(fn () => CustomerGroup::active()->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Leave empty for regular customers without group discounts.'),
                    ]),
                Section::make('Marketing Preferences')
                    ->description('Manage marketing communication settings.')
                    ->icon('heroicon-o-envelope-open')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Toggle::make('marketing_opt_in')
                            ->label('Receive Marketing Emails')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->offColor('danger')
                            ->helperText('Enable to receive promotional offers and newsletters.')
                            ->afterStateUpdated(function ($state, $record) {
                                if ($record) {
                                    $record->update([
                                        'marketing_opt_in_at' => $state ? now() : null,
                                    ]);
                                }
                            }),
                    ]),
                Section::make('Security')
                    ->description('Manage authentication details and critical security milestones.')
                    ->icon('heroicon-o-lock-closed')
                    ->columns([
                        'sm' => 2,
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Leave blank to keep the current password.'),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email verified at')
                            ->placeholder('Select date & time')
                            ->seconds(false)
                            ->helperText('Set when manually verifying an address.')
                            ->columnSpan([
                                'sm' => 2,
                                'md' => 1,
                            ]),
                        DateTimePicker::make('two_factor_confirmed_at')
                            ->label('2FA confirmed at')
                            ->placeholder('Select date & time')
                            ->seconds(false)
                            ->helperText('Managed automatically once the user enables 2FA.')
                            ->columnSpan([
                                'sm' => 2,
                                'md' => 1,
                            ]),
                    ]),
                Section::make('Social Accounts')
                    ->description('Reference IDs supplied by OAuth providers.')
                    ->icon('heroicon-o-share')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('google_id')
                            ->label('Google ID')
                            ->placeholder('Filled automatically after Google sign-in.')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('facebook_id')
                            ->label('Facebook ID')
                            ->placeholder('Filled automatically after Facebook sign-in.')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Two-factor Backups')
                    ->description('Sensitive secrets generated when two-factor authentication is active.')
                    ->icon('heroicon-o-key')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('two_factor_secret')
                            ->label('Two-factor secret')
                            ->rows(3)
                            ->placeholder('Generated once the user enables 2FA.')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('two_factor_recovery_codes')
                            ->label('Recovery codes')
                            ->rows(4)
                            ->placeholder('Generated once the user enables 2FA.')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
