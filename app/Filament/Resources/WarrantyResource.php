<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarrantyResource\Pages;
use App\Filament\Resources\WarrantyResource\Schemas\WarrantyForm;
use App\Filament\Resources\WarrantyResource\Schemas\WarrantyInfolist;
use App\Filament\Resources\WarrantyResource\Tables\WarrantiesTable;
use App\Models\Sales\Warranty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarrantyResource extends Resource
{
    protected static ?string $model = Warranty::class;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $recordTitleAttribute = 'warranty_number';

    protected static ?string $pluralModelLabel = 'Warranties';

    protected static ?string $modelLabel = 'Warranty';

    public static function form(Schema $schema): Schema
    {
        return WarrantyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(WarrantyInfolist::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(WarrantiesTable::getColumns())
            ->filters(WarrantiesTable::getFilters())
            ->actions(WarrantiesTable::getActions())
            ->bulkActions(WarrantiesTable::getBulkActions());
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
            'index' => Pages\ListWarranties::route('/'),
            'create' => Pages\CreateWarranty::route('/create'),
            'view' => Pages\ViewWarranty::route('/{record}'),
            'edit' => Pages\EditWarranty::route('/{record}/edit'),
        ];
    }
}