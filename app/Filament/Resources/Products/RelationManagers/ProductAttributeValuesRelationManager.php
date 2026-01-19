<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductAttributeValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributeValues';

    protected static ?string $recordTitleAttribute = 'value';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('attribute_id')
                    ->label('Attribute')
                    ->relationship('attribute', 'name', function ($query) {
                        $product = $this->getOwnerRecord();
                        if ($product->attribute_family_id) {
                            $query->where('attribute_family_id', $product->attribute_family_id);
                        }

                        return $query->where('is_active', true);
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Clear value when attribute changes
                        $set('value', '');
                    })
                    ->helperText('Select the attribute'),

                Select::make('value')
                    ->label('Value')
                    ->options(function ($get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return [];
                        }

                        $attribute = Attribute::find($attributeId);
                        if (! $attribute || $attribute->type !== AttributeType::Predefined) {
                            return [];
                        }

                        return $attribute->attributeValues()
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('value', 'value')
                            ->toArray();
                    })
                    ->visible(function ($get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return false;
                        }
                        $attribute = Attribute::find($attributeId);

                        return $attribute && $attribute->type === AttributeType::Predefined;
                    })
                    ->searchable()
                    ->required(function ($get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return false;
                        }
                        $attribute = Attribute::find($attributeId);

                        return $attribute && $attribute->type === AttributeType::Predefined;
                    })
                    ->helperText('Select a predefined value'),

                TextInput::make('value')
                    ->label('Value')
                    ->visible(function ($get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return false;
                        }
                        $attribute = Attribute::find($attributeId);

                        return $attribute && $attribute->type === AttributeType::Manual;
                    })
                    ->required(function ($get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return false;
                        }
                        $attribute = Attribute::find($attributeId);

                        return $attribute && $attribute->type === AttributeType::Manual;
                    })
                    ->maxLength(255)
                    ->helperText('Enter the attribute value'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-list-bullet')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->copyMessage('Value copied!')
                    ->copyMessageDuration(1500),

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
                        $data['product_id'] = $this->getOwnerRecord()->id;

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
                        $data['product_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return filled($ownerRecord->attribute_family_id);
    }
}
