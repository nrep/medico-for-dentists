<?php

namespace App\Filament\Resources\MigrationErrorResource\Pages;

use App\Filament\Resources\MigrationErrorResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMigrationError extends EditRecord
{
    protected static string $resource = MigrationErrorResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
