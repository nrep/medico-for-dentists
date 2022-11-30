<?php

namespace App\Filament\Resources\InvoiceDayResource\Pages;

use App\Filament\Resources\InvoiceDayResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoiceDays extends ListRecords
{
    protected static string $resource = InvoiceDayResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
