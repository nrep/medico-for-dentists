<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Accountancy\Filament\Resources\BudgetAccountTransactionResource;

class ListBudgetAccountTransactions extends ListRecords
{
    protected static string $resource = BudgetAccountTransactionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
