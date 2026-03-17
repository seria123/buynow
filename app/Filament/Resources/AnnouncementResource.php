<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Filament\Resources\AnnouncementResource\RelationManagers\AnnouncementRecipientsRelationManager;
use App\Filament\Resources\AnnouncementResource\Schemas\AnnouncementForm;
use App\Filament\Resources\AnnouncementResource\Schemas\AnnouncementInfolist;
use App\Filament\Resources\AnnouncementResource\Tables\AnnouncementsTable;
use App\Models\Announcement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $pluralModelLabel = 'Announcements';

    protected static ?string $modelLabel = 'Announcement';

    public static function form(Schema $schema): Schema
    {
        return AnnouncementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components(AnnouncementInfolist::class);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(AnnouncementsTable::getColumns())
            ->filters(AnnouncementsTable::getFilters())
            ->actions(AnnouncementsTable::getActions())
            ->bulkActions(AnnouncementsTable::getBulkActions());
    }

    public static function getRelations(): array
    {
        return [
            AnnouncementRecipientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
        ];
    }
}
