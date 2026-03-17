<?php

namespace App\Filament\Resources\AnnouncementResource\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Schema;

class AnnouncementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Announcement Details')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title'),
                        TextEntry::make('type_label')
                            ->label('Type'),
                        TextEntry::make('target_label')
                            ->label('Target Audience'),
                        TextEntry::make('customerGroup.name')
                            ->label('Customer Group')
                            ->visible(fn($record) => $record->target === 'customer_group'),
                        TextEntry::make('user.name')
                            ->label('Customer')
                            ->visible(fn($record) => $record->target === 'individual'),
                    ])
                    ->columns(2),
                Section::make('Content')
                    ->schema([
                        TextEntry::make('content')
                            ->label('')
                            ->html()
                            ->prose(),
                    ]),
                Section::make('Status & Statistics')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray',
                                'scheduled' => 'warning',
                                'sent' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('scheduled_at')
                            ->label('Scheduled For')
                            ->dateTime(),
                        TextEntry::make('sent_at')
                            ->label('Sent At')
                            ->dateTime(),
                        TextEntry::make('recipients_count')
                            ->label('Recipients'),
                        TextEntry::make('sent_count')
                            ->label('Sent'),
                        TextEntry::make('opened_count')
                            ->label('Opened'),
                        TextEntry::make('creator.name')
                            ->label('Created By'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ])
                    ->columns(3),
            ]);
    }
}
