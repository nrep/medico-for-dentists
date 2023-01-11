<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Modules\Accountancy\Filament\Resources\BudgetAccountTransactionResource;

class CreateBudgetAccountTransaction extends CreateRecord
{
    protected static string $resource = BudgetAccountTransactionResource::class;
}
