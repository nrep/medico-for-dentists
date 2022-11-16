<?php

namespace App\Filament\Resources\FilelessInvoiceResource\Pages;

use App\Filament\Resources\FilelessInvoiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilelessInvoice extends EditRecord
{
    protected static string $resource = FilelessInvoiceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
