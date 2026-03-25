<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function table(Table $table): Table
    {
        return TransactionsTable::table($table);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}