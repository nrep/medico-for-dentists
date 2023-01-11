<?php

namespace Modules\Accountancy\Filament\Resources\SupplierResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Accountancy\Filament\Resources\SupplierResource;

class ManageSuppliers extends ManageRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
