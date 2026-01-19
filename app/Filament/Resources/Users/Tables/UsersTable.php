<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ))
                    ->tooltip('User avatar'),

                TextColumn::make('name')
                    ->label('User Name')
                    ->searchable(['name', 'email', 'username'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->description(fn ($record): ?string => $record->email)
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Name copied!')
                    ->copyMessageDuration(1500)
                    ->tooltip(fn ($record): string => 'User: '.$record->name),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->iconColor('info')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500)
                    ->tooltip(fn ($record): string => 'Email: '.$record->email)
                    ->url(fn ($record): string => 'mailto:'.$record->email)
                    ->openUrlInNewTab(),

                TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-at-symbol')
                    ->iconColor('gray')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->copyable()
                    ->copyMessage('Username copied!')
                    ->copyMessageDuration(1500)
                    ->tooltip(fn ($record): ?string => $record->username ? 'Username: @'.$record->username : 'No username set'),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->limitList(3)
                    ->icon('heroicon-o-shield-check')
                    ->tooltip(fn ($record): string => $record->roles->pluck('name')->join(', ') ?: 'No roles assigned')
                    ->searchable(),

                TextColumn::make('roles')
                    ->label('Roles Count')
                    ->formatStateUsing(fn ($record) => $record->roles->count())
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-shield-check')
                    ->sortable()
                    ->tooltip(fn ($record): string => $record->roles->count().' role(s) assigned'),

                TextColumn::make('permissions')
                    ->label('Permissions')
                    ->formatStateUsing(fn ($record) => $record->getAllPermissions()->count())
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-key')
                    ->tooltip(fn ($record): string => $record->getAllPermissions()->count().' permission(s) total (direct + via roles)'),

                IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state, $record): string => $state
                        ? 'Email verified on '.$record->email_verified_at?->format('M d, Y H:i')
                        : 'Email not verified'),

                IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state, $record): string => $state
                        ? 'Two-factor authentication enabled on '.$record->two_factor_confirmed_at?->format('M d, Y H:i')
                        : 'Two-factor authentication not enabled'),

                TextColumn::make('social_login')
                    ->label('Social Login')
                    ->formatStateUsing(function ($record) {
                        $providers = [];
                        if ($record->google_id) {
                            $providers[] = 'Google';
                        }
                        if ($record->facebook_id) {
                            $providers[] = 'Facebook';
                        }

                        return $providers ? implode(', ', $providers) : 'Email';
                    })
                    ->badge()
                    ->color(fn ($record) => $record->google_id || $record->facebook_id ? 'info' : 'gray')
                    ->icon(fn ($record) => $record->google_id || $record->facebook_id ? 'heroicon-o-globe-alt' : 'heroicon-o-envelope')
                    ->tooltip(function ($record): string {
                        $providers = [];
                        if ($record->google_id) {
                            $providers[] = 'Google';
                        }
                        if ($record->facebook_id) {
                            $providers[] = 'Facebook';
                        }

                        return $providers ? 'Connected via: '.implode(', ', $providers) : 'Email login only';
                    }),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return '—';
                        }
                        $carbon = \Carbon\Carbon::parse($state);
                        $date = $carbon->format('M d, Y');
                        $relative = $carbon->diffForHumans();

                        return '<div class="space-y-0.5">'.
                            '<div class="font-semibold text-sm">'.$date.'</div>'.
                            '<div class="text-xs text-gray-500">'.$relative.'</div>'.
                            '</div>';
                    })
                    ->html()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('info')
                    ->tooltip(fn ($record): string => $record->created_at?->format('l, F j, Y \a\t g:i A') ?? 'Unknown'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->iconColor('warning')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record): string => $record->updated_at?->format('l, F j, Y \a\t g:i A') ?? 'Unknown'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->placeholder('All users')
                    ->trueLabel('Verified')
                    ->falseLabel('Not verified'),

                TernaryFilter::make('two_factor_confirmed_at')
                    ->label('Two-Factor Auth')
                    ->placeholder('All users')
                    ->trueLabel('2FA Enabled')
                    ->falseLabel('2FA Disabled'),

                SelectFilter::make('has_social_login')
                    ->label('Social Login')
                    ->options([
                        'google' => 'Has Google',
                        'facebook' => 'Has Facebook',
                        'both' => 'Has Both',
                        'email_only' => 'Email Only',
                    ])
                    ->query(function ($query, $state) {
                        return match ($state) {
                            'google' => $query->whereNotNull('google_id')->whereNull('facebook_id'),
                            'facebook' => $query->whereNotNull('facebook_id')->whereNull('google_id'),
                            'both' => $query->whereNotNull('google_id')->whereNotNull('facebook_id'),
                            'email_only' => $query->whereNull('google_id')->whereNull('facebook_id'),
                            default => $query,
                        };
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    BulkAction::make('verify_emails')
                        ->label('Verify Emails')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['email_verified_at' => now()]))
                        ->successNotificationTitle('Emails verified'),
                    BulkAction::make('unverify_emails')
                        ->label('Unverify Emails')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['email_verified_at' => null]))
                        ->successNotificationTitle('Emails unverified'),
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete())
                        ->successNotificationTitle('Users deleted'),
                ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->tooltip('View user'),
                    \Filament\Actions\EditAction::make()
                        ->tooltip('Edit user'),
                    Action::make('toggle_email_verification')
                        ->label(fn ($record) => $record->email_verified_at ? 'Unverify Email' : 'Verify Email')
                        ->icon(fn ($record) => $record->email_verified_at ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->email_verified_at ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->tooltip(fn ($record) => $record->email_verified_at ? 'Unverify email address' : 'Verify email address')
                        ->action(fn ($record) => $record->update([
                            'email_verified_at' => $record->email_verified_at ? null : now(),
                        ]))
                        ->successNotificationTitle(fn ($record) => $record->email_verified_at ? 'Email unverified' : 'Email verified'),
                    \Filament\Actions\DeleteAction::make()
                        ->tooltip('Delete user'),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-user-plus')
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Create your first user to get started managing your team.');
    }
}
