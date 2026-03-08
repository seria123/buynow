<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View Details')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }
}
