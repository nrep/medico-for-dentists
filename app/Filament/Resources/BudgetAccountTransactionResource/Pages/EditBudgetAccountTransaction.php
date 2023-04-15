<?php

namespace App\Filament\Resources\BudgetAccountTransactionResource\Pages;

use App\Filament\Resources\BudgetAccountTransactionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetAccountTransaction extends EditRecord
{
    protected static string $resource = BudgetAccountTransactionResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
