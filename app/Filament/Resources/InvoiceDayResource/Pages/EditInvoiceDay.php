<?php

namespace App\Filament\Resources\InvoiceDayResource\Pages;

use App\Filament\Resources\InvoiceDayResource;
use App\Models\InvoiceDay;
use App\Models\Session;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceDay extends EditRecord
{
    protected static string $resource = InvoiceDayResource::class;

    public Session $session;

    public function mount($record): void
    {
        $this->session = InvoiceDay::find($record)->invoice->session;
        parent::mount($record);
    }

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
