<?php

namespace App\Filament\Resources\Attributes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttributeValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributeValues';

    protected static ?string $recordTitleAttribute = 'value';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('value')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn ($state, callable $set) => $set('slug', Str::slug($state))
                    )
                    ->helperText('The display value (e.g., "Black", "White", "128GB")'),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                        $attributeId = $this->getOwnerRecord()->id;

                        return $rule->where('attribute_id', $attributeId);
                    })
                    ->helperText('Auto-generated from value, but you can customize it'),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(function () {
                        $attributeId = $this->getOwnerRecord()->id;
                        $maxSort = \App\Models\Catalogue\AttributeValue::query()
                            ->where('attribute_id', $attributeId)
                            ->max('sort_order');

                        return is_numeric($maxSort) ? $maxSort + 1 : 0;
                    })
                    ->required()
                    ->minValue(0)
                    ->helperText('Lower numbers appear first'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive values won\'t be visible'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->slug)
                    ->copyable()
                    ->copyMessage('Value copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-arrows-up-down'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable()
                    ->tooltip(fn ($state) => $state ? 'Active' : 'Inactive'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('info')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['creator_id'] = Auth::id();
                        $data['attribute_id'] = $this->getOwnerRecord()->id;

                        // Auto-increment sort_order
                        $attributeId = $this->getOwnerRecord()->id;
                        $maxSort = \App\Models\Catalogue\AttributeValue::query()
                            ->where('attribute_id', $attributeId)
                            ->max('sort_order');
                        $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['creator_id'] = Auth::id();
                        $data['attribute_id'] = $this->getOwnerRecord()->id;

                        // Auto-increment sort_order
                        $attributeId = $this->getOwnerRecord()->id;
                        $maxSort = \App\Models\Catalogue\AttributeValue::query()
                            ->where('attribute_id', $attributeId)
                            ->max('sort_order');
                        $data['sort_order'] = is_numeric($maxSort) ? $maxSort + 1 : 0;

                        return $data;
                    }),
            ]);
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        $type = $ownerRecord->type;
        if ($type instanceof \App\Models\Catalogue\AttributeType) {
            return $type === \App\Models\Catalogue\AttributeType::Predefined;
        }

        return $type === 'predefined' || $type === \App\Models\Catalogue\AttributeType::Predefined->value;
    }

    public function isReadOnly(): bool
    {
        $type = $this->getOwnerRecord()->type;
        if ($type instanceof \App\Models\Catalogue\AttributeType) {
            return $type !== \App\Models\Catalogue\AttributeType::Predefined;
        }

        return $type !== 'predefined' && $type !== \App\Models\Catalogue\AttributeType::Predefined->value;
    }
}
