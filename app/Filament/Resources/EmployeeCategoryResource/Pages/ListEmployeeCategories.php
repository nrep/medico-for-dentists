<?php

namespace App\Filament\Resources\EmployeeCategoryResource\Pages;

use App\Filament\Resources\EmployeeCategoryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeCategories extends ListRecords
{
    protected static string $resource = EmployeeCategoryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New category')
                ->icon('heroicon-s-plus-circle'),
        ];
    }
}
