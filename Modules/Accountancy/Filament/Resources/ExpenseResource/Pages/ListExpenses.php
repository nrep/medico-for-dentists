<?php

namespace Modules\Accountancy\Filament\Resources\ExpenseResource\Pages;

use Modules\Accountancy\Filament\Resources\ExpenseResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
