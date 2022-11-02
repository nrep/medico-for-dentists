<?php

namespace App\Filament\Resources\ChargeListResource\Pages;

use App\Filament\Resources\ChargeListResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChargeLists extends ListRecords
{
    protected static string $resource = ChargeListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-plus-circle'),
        ];
    }
}
