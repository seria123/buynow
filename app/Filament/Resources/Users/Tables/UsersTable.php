<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->height(50)
                    ->width(50)
                    ->getStateUsing(fn ($record): ?string => $record->getFirstMediaUrl('avatar'))
                    ->defaultImageUrl(fn ($record): string => sprintf(
                        'https://ui-avatars.com/api/?name=%s&background=F3F4F6&color=111827',
                        urlencode((string) ($record->name ?? 'User'))
                    )),
                TextColumn::make('name')
                    ->label('User')
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->email)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->limitList(3)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('email_verified_at')
                    ->label('Email verified')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->tooltip(fn ($state): string => $state ? 'Two-factor enabled' : 'Two-factor not enabled')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->tooltip(fn ($record): string => $record->created_at?->diffForHumans() ?? 'Unknown')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple(),
                TernaryFilter::make('email_verified_at')
                    ->label('Email verified')
                    ->placeholder('All users')
                    ->trueLabel('Verified')
                    ->falseLabel('Not verified'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateIcon('heroicon-o-user-plus')
            ->emptyStateHeading('Invite your first teammate')
            ->emptyStateDescription('As users join your workspace, you can manage their profile, roles, and security details here.');
    }
}
