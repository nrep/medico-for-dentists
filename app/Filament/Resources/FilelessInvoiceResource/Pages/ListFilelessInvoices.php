<?php

namespace App\Filament\Resources\FilelessInvoiceResource\Pages;

use App\Filament\Resources\FilelessInvoiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilelessInvoices extends ListRecords
{
    protected static string $resource = FilelessInvoiceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-plus-circle'),
        ];
    }
}
