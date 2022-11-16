<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceInsuranceTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoicePatientTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceTotalPrice;
use App\Models\Invoice;
use App\Models\Session;
use Filament\Pages\Actions;
use Filament\Pages\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public ?Session $session = null;

    public function mount($record): void
    {
        $this->session = Invoice::find($record)?->session;
        parent::mount($record);
    }

    protected function getActions(): array
    {
        return [
            ViewAction::make()
                ->icon('heroicon-s-eye'),
            Actions\DeleteAction::make()
                ->icon('heroicon-s-trash'),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceTotalPrice::class,
            InvoiceInsuranceTotalPrice::class,
            InvoicePatientTotalPrice::class
        ];
    }

    protected function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }
}
