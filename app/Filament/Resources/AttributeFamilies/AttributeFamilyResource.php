<?php

namespace App\Filament\Resources\AttributeFamilies;

use App\Filament\Resources\AttributeFamilies\Pages\CreateAttributeFamily;
use App\Filament\Resources\AttributeFamilies\Pages\EditAttributeFamily;
use App\Filament\Resources\AttributeFamilies\Pages\ListAttributeFamilies;
use App\Filament\Resources\AttributeFamilies\Pages\ViewAttributeFamily;
use App\Filament\Resources\AttributeFamilies\Schemas\AttributeFamilyForm;
use App\Filament\Resources\AttributeFamilies\Schemas\AttributeFamilyInfolist;
use App\Filament\Resources\AttributeFamilies\Tables\AttributeFamiliesTable;
use App\Models\Catalogue\AttributeFamily;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttributeFamilyResource extends Resource
{
    protected static ?string $model = AttributeFamily::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AttributeFamilyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttributeFamilyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttributeFamiliesTable::configure($table);
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
            'index' => ListAttributeFamilies::route('/'),
            'create' => CreateAttributeFamily::route('/create'),
            'view' => ViewAttributeFamily::route('/{record}'),
            'edit' => EditAttributeFamily::route('/{record}/edit'),
        ];
    }
}
