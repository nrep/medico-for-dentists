<?php

namespace App\Filament\Resources\DebtResource\Pages;

use App\Filament\Resources\DebtResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDebt extends ViewRecord
{
    protected static string $resource = DebtResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
