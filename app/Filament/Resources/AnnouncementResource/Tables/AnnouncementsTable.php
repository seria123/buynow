<?php

namespace App\Filament\Resources\AnnouncementResource\Tables;

use App\Models\Announcement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function getColumns(): array
    {
        return [
            TextColumn::make('title')
                ->label('Title')
                ->searchable()
                ->sortable()
                ->limit(50),
            BadgeColumn::make('type')
                ->label('Type')
                ->colors([
                    'gray' => 'general',
                    'info' => 'promotional',
                    'warning' => 'order',
                    'success' => 'account',
                    'primary' => 'newsletter',
                ]),
            BadgeColumn::make('target')
                ->label('Target')
                ->colors([
                    'gray' => 'all',
                    'info' => 'customer_group',
                    'success' => 'individual',
                ]),
            TextColumn::make('customerGroup.name')
                ->label('Group')
                ->visible(fn($record) => $record?->target === 'customer_group'),
            BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'gray' => 'draft',
                    'warning' => 'scheduled',
                    'success' => 'sent',
                    'danger' => 'cancelled',
                ]),
            TextColumn::make('sent_count')
                ->label('Sent')
                ->numeric(),
            TextColumn::make('opened_count')
                ->label('Opened')
                ->numeric(),
            TextColumn::make('scheduled_at')
                ->label('Scheduled')
                ->dateTime()
                ->sortable(),
            TextColumn::make('sent_at')
                ->label('Sent At')
                ->dateTime()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable(),
        ];
    }

    public static function getFilters(): array
    {
        return [
            SelectFilter::make('type')
                ->label('Type')
                ->options([
                    'general' => 'General',
                    'promotional' => 'Promotional',
                    'order' => 'Order Related',
                    'account' => 'Account',
                    'newsletter' => 'Newsletter',
                ]),
            SelectFilter::make('target')
                ->label('Target')
                ->options([
                    'all' => 'All Customers',
                    'customer_group' => 'Customer Group',
                    'individual' => 'Individual',
                ]),
            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'sent' => 'Sent',
                    'cancelled' => 'Cancelled',
                ]),
        ];
    }

    public static function getActions(): array
    {
        return [
            ViewAction::make(),
            EditAction::make(),
            Action::make('send')
                ->label('Send')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn(Announcement $record) => in_array($record->status, ['draft', 'scheduled']))
                ->requiresConfirmation()
                ->action(fn(Announcement $record) => $record->send()),
            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->visible(fn(Announcement $record) => $record->status === 'scheduled')
                ->requiresConfirmation()
                ->action(fn(Announcement $record) => $record->cancel()),
            DeleteAction::make(),
        ];
    }

    public static function getBulkActions(): array
    {
        return [
            //
        ];
    }
}
