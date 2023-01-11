<?php

namespace Modules\Accountancy\Filament\Resources\BudgetLineResource\Pages;

use Modules\Accountancy\Filament\Resources\BudgetLineResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetLines extends ListRecords
{
    protected static string $resource = BudgetLineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
