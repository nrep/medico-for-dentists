<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Accountancy\Filament\Resources\BudgetAccountTransactionResource;

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
