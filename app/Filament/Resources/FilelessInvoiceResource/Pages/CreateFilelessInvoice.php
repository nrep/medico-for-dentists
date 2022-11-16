<?php

namespace App\Filament\Resources\FilelessInvoiceResource\Pages;

use App\Filament\Resources\FilelessInvoiceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFilelessInvoice extends CreateRecord
{
    protected static string $resource = FilelessInvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['done_by'] = auth()->id();
        
        return $data;
    }
}
