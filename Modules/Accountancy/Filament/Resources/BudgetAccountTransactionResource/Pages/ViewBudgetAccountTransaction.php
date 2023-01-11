<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Accountancy\Filament\Resources\BudgetAccountTransactionResource;

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
