<?php

namespace App\Filament\Resources\InventorySources;

use App\Filament\Resources\InventorySources\Pages\CreateInventorySource;
use App\Filament\Resources\InventorySources\Pages\EditInventorySource;
use App\Filament\Resources\InventorySources\Pages\ListInventorySources;
use App\Filament\Resources\InventorySources\Pages\ViewInventorySource;
use App\Filament\Resources\InventorySources\Schemas\InventorySourceForm;
use App\Filament\Resources\InventorySources\Schemas\InventorySourceInfolist;
use App\Filament\Resources\InventorySources\Tables\InventorySourcesTable;
use App\Models\Inventory\InventorySource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventorySourceResource extends Resource
{
    protected static ?string $model = InventorySource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InventorySourceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventorySourceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventorySourcesTable::configure($table);
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
            'index' => ListInventorySources::route('/'),
            'create' => CreateInventorySource::route('/create'),
            'view' => ViewInventorySource::route('/{record}'),
            'edit' => EditInventorySource::route('/{record}/edit'),
        ];
    }
}
