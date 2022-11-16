<?php

namespace App\Filament\Resources\FilelessInvoiceResource\Pages;

use App\Filament\Resources\FilelessInvoiceResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFilelessInvoice extends ViewRecord
{
    protected static string $resource = FilelessInvoiceResource::class;

    protected static string $view = 'view-fileless-invoice';

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return "Print";
                    }
                    // return "Preview";
                    return "Print";
                })
                ->color(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return "primary";
                    }
                    return "primary";
                })
                // ->icon('fas-print')
                ->disabled(function ($record) {
                    /* if ($this->record->payments()->count('id') > 0) {
                        return false;
                    } */
                    return false;
                })
                ->action('printInvonce'),
            ActionGroup::make([
                EditAction::make()
                    ->icon('heroicon-s-pencil')
                    ->color('secondary'),
                Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
                Actions\ForceDeleteAction::make(),
                Actions\RestoreAction::make(),
            ]),
        ];
    }

    public function printInvonce()
    {
        $this->dispatchBrowserEvent('print-invoice');
    }
}
