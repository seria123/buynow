<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\Schemas\PromotionForm;
use App\Filament\Resources\PromotionResource\Schemas\PromotionInfolist;
use App\Filament\Resources\PromotionResource\Tables\PromotionsTable;
use App\Models\Sales\Promotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Campaigns';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $pluralModelLabel = 'Promotions';

    protected static ?string $modelLabel = 'Promotion';

    public static function form(Schema $schema): Schema
    {
        return PromotionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(PromotionInfolist::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(PromotionsTable::getColumns())
            ->filters(PromotionsTable::getFilters())
            ->actions(PromotionsTable::getActions())
            ->bulkActions(PromotionsTable::getBulkActions());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'view' => Pages\ViewPromotion::route('/{record}'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
