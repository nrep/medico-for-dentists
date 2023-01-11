<?php

namespace App\Filament\Resources\BudgetLineResource\Pages;

use App\Filament\Resources\BudgetAccountTransactionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBudgetAccountTransaction extends CreateRecord
{
    protected static string $resource = BudgetAccountTransactionResource::class;
}
