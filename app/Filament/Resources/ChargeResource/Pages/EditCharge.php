<?php

namespace App\Filament\Resources\ChargeResource\Pages;

use App\Filament\Resources\ChargeResource;
use App\Models\ChargeListChargeType;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCharge extends EditRecord
{
    protected static string $resource = ChargeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['charge_list'] = ChargeListChargeType::find($data['charge_list_charge_type_id'])->chargeList->id;

        return $data;
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
