<?php

namespace App\Filament\Resources\AttributeValues\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeValuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->slug)
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Value copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-list-bullet')
                    ->badge()
                    ->color('info')
                    ->tooltip(fn ($record) => 'Attribute: '.$record->attribute?->name),

                TextColumn::make('attribute.attributeFamily.name')
                    ->label('Family')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->badge()
                    ->color('gray')
                    ->tooltip(fn ($record) => 'Attribute Family: '.$record->attribute?->attributeFamily?->name),

                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-arrows-up-down'),

                TextColumn::make('creator.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->placeholder('Unknown')
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
                        ->successNotificationTitle('Attribute values activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->successNotificationTitle('Attribute values deactivated'),
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete())
                        ->successNotificationTitle('Attribute values deleted'),
                ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->tooltip('View attribute value'),
                    \Filament\Actions\EditAction::make()
                        ->tooltip('Edit attribute value'),
                    Action::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->tooltip(fn ($record) => $record->is_active ? 'Deactivate attribute value' : 'Activate attribute value')
                        ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                        ->successNotificationTitle(fn ($record) => $record->is_active ? 'Attribute value deactivated' : 'Attribute value activated'),
                    \Filament\Actions\DeleteAction::make()
                        ->tooltip('Delete attribute value'),
                ]),
            ]);
    }
}
