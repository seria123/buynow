<?php

namespace App\Filament\Resources\AnnouncementResource\RelationManagers;

use App\Models\User;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Filters\SelectFilter;

class AnnouncementRecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->email),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('info'),

                TextColumn::make('pivot.sent_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-paper-airplane')
                    ->iconColor('success'),

                TextColumn::make('pivot.opened_at')
                    ->label('Opened At')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-eye')
                    ->iconColor('primary')
                    ->placeholder('Not opened yet'),

                IconColumn::make('pivot.opened_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter()
                    ->getStateUsing(fn ($record): bool => $record->pivot->opened_at !== null),
            ])
            ->filters([
                SelectFilter::make('opened')
                    ->label('Open Status')
                    ->options([
                        'opened' => 'Opened',
                        'not_opened' => 'Not Opened',
                    ])
                    ->query(function ($query, $state) {
                        return match ($state) {
                            'opened' => $query->whereNotNull('announcement_recipients.opened_at'),
                            'not_opened' => $query->whereNull('announcement_recipients.opened_at'),
                            default => $query,
                        };
                    }),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
