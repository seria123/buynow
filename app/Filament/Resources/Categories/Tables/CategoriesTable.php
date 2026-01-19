<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')
                    ->label('Icon')
                    ->icon(fn (?string $state) => self::iconAlias($state) ?? 'heroicon-o-rectangle-stack')
                    ->tooltip(fn ($record) => $record->icon ? 'Custom icon' : 'Defaults to rectangle stack')
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable(['name', 'slug'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-tag')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->slug)
                    ->tooltip(fn ($record) => $record->description ?? 'No description')
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Name copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('parent_id')
                    ->label('Parent')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-folder')
                    ->formatStateUsing(fn ($state, $record) => $record->parent?->name ?? 'Top Level')
                    ->tooltip(fn ($record) => $record->parent?->name ? 'Parent: '.$record->parent->name : 'Top Level category'),

                TextColumn::make('creator_id')
                    ->label('Creator')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->creator?->name ?? 'Unknown')
                    ->tooltip(fn ($record) => $record->creator?->name ? 'Created by '.$record->creator->name : 'Creator unknown'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state) => $state ? 'Active' : 'Inactive'),

                TextColumn::make('created_at')
                    ->label('Created')
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
                    ->tooltip(fn ($record) => $record->created_at?->format('l, F j, Y \a\t g:i A')),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->successNotificationTitle('Categories activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->successNotificationTitle('Categories deactivated'),
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete())
                        ->successNotificationTitle('Categories deleted'),
                ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->tooltip('View category'),
                    \Filament\Actions\EditAction::make()
                        ->tooltip('Edit category'),
                    Action::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->tooltip(fn ($record) => $record->is_active ? 'Deactivate category' : 'Activate category')
                        ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                        ->successNotificationTitle(fn ($record) => $record->is_active ? 'Category deactivated' : 'Category activated'),
                    \Filament\Actions\DeleteAction::make()
                        ->tooltip('Delete category'),
                ]),
            ]);
    }

    private static function iconAlias(?string $icon): ?string
    {
        if (! $icon) {
            return null;
        }

        return str_starts_with($icon, 'o-')
            ? 'heroicon-'.$icon
            : 'heroicon-m-'.$icon;
    }
}
