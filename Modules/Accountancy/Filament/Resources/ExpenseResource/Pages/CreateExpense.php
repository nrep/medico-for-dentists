<?php

namespace Modules\Accountancy\Filament\Resources\ExpenseResource\Pages;

use Modules\Accountancy\Filament\Resources\ExpenseResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}
