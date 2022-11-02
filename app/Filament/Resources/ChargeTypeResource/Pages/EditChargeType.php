<?php

namespace App\Filament\Resources\ChargeTypeResource\Pages;

use App\Filament\Resources\ChargeTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChargeType extends EditRecord
{
    protected static string $resource = ChargeTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
