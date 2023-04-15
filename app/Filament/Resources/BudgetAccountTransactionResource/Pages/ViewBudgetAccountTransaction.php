<?php

namespace App\Filament\Resources\BudgetAccountTransactionResource\Pages;

use App\Filament\Resources\BudgetAccountTransactionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBudgetAccountTransaction extends ViewRecord
{
    protected static string $resource = BudgetAccountTransactionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
