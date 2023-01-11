<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Modules\Accountancy\Filament\Resources\BudgetLineResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetLine extends EditRecord
{
    protected static string $resource = BudgetLineResource::class;

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
