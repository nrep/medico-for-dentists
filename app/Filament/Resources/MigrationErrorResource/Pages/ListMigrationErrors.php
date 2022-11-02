<?php

namespace App\Filament\Resources\MigrationErrorResource\Pages;

use App\Filament\Resources\MigrationErrorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMigrationErrors extends ListRecords
{
    protected static string $resource = MigrationErrorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
        ];
    }
}
