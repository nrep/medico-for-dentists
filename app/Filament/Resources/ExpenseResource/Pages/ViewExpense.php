<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    protected static string $view = 'view-expense';

    protected function getActions(): array
    {
        return [
            CreateAction::make('new')
                ->action(fn () => redirect($this->getResource()::getUrl('create')))
                ->color('secondary'),
            Actions\EditAction::make()
                ->color('secondary'),
            Action::make('print')
                ->action('printInvonce'),
        ];
    }

    public function printInvonce()
    {
        $this->dispatchBrowserEvent('print-invoice');
    }
}
