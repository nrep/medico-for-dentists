<?php

namespace App\Filament\Resources\InvoiceDayResource\Pages;

use App\Filament\Resources\InvoiceDayResource;
use App\Models\InvoiceDay;
use App\Models\Session;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoiceDay extends ViewRecord
{
    protected static string $resource = InvoiceDayResource::class;

    protected static string $view = 'view-invoice-day';

    public Session $session;

    public function mount($record): void
    {
        $this->session = InvoiceDay::find($record)->invoice->session;
        parent::mount($record);
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
