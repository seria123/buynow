<?php

namespace App\Filament\Resources\AdResource;

use App\Filament\Resources\AdResource\Pages;
use App\Filament\Resources\AdResource\Schemas\AdForm;
use App\Filament\Resources\AdResource\Schemas\AdInfolist;
use App\Filament\Resources\AdResource\Tables\AdsTable;
use App\Models\Ad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Campaigns';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $pluralModelLabel = 'Ads';

    protected static ?string $modelLabel = 'Ad';

    public static function form(Schema $schema): Schema
    {
        return AdForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(AdInfolist::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(AdsTable::getColumns())
            ->filters(AdsTable::getFilters())
            ->actions(AdsTable::getActions())
            ->bulkActions(AdsTable::getBulkActions());
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
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'view' => Pages\ViewAd::route('/{record}'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}
