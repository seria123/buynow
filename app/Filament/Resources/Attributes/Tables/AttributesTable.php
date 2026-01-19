<?php

namespace App\Filament\Resources\Attributes\Tables;

use App\Models\Catalogue\AttributeType;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Attribute Name')
                    ->searchable(['name', 'slug'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-list-bullet')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->slug)
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Name copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('attributeFamily.name')
                    ->label('Family')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->badge()
                    ->color('info')
                    ->tooltip(fn ($record) => 'Attribute Family: '.$record->attributeFamily?->name),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof AttributeType) {
                            return $state->label();
                        }

                        return $state === 'predefined' || $state === AttributeType::Predefined->value ? 'Predefined' : 'Manual';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $isPredefined = $state instanceof AttributeType
                            ? $state === AttributeType::Predefined
                            : ($state === 'predefined' || $state === AttributeType::Predefined->value);

                        return $isPredefined ? 'success' : 'warning';
                    })
                    ->icon(function ($state) {
                        $isPredefined = $state instanceof AttributeType
                            ? $state === AttributeType::Predefined
                            : ($state === 'predefined' || $state === AttributeType::Predefined->value);

                        return $isPredefined ? 'heroicon-o-check-circle' : 'heroicon-o-pencil';
                    })
                    ->sortable()
                    ->tooltip(function ($state) {
                        $isPredefined = $state instanceof AttributeType
                            ? $state === AttributeType::Predefined
                            : ($state === 'predefined' || $state === AttributeType::Predefined->value);

                        return $isPredefined ? 'Has predefined values' : 'Manual entry';
                    }),

                TextColumn::make('attributeValues')
                    ->label('Values')
                    ->formatStateUsing(fn ($record) => $record?->attributeValues?->count() ?? 0)
                    ->sortable()
                    ->icon('heroicon-o-check-circle')
                    ->badge()
                    ->color('info')
                    ->visible(function ($record) {
                        $type = $record?->type;
                        if ($type instanceof AttributeType) {
                            return $type === AttributeType::Predefined;
                        }

                        return $type === 'predefined' || $type === AttributeType::Predefined->value;
                    })
                    ->tooltip('Number of predefined values'),

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
                        ->successNotificationTitle('Attributes activated'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->successNotificationTitle('Attributes deactivated'),
                    BulkAction::make('delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete())
                        ->successNotificationTitle('Attributes deleted'),
                ]),
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\ViewAction::make()
                        ->tooltip('View attribute'),
                    \Filament\Actions\EditAction::make()
                        ->tooltip('Edit attribute'),
                    Action::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->tooltip(fn ($record) => $record->is_active ? 'Deactivate attribute' : 'Activate attribute')
                        ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                        ->successNotificationTitle(fn ($record) => $record->is_active ? 'Attribute deactivated' : 'Attribute activated'),
                    \Filament\Actions\DeleteAction::make()
                        ->tooltip('Delete attribute'),
                ]),
            ]);
    }
}
