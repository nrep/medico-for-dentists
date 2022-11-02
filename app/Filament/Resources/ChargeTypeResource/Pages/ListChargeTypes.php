<?php

namespace App\Filament\Resources\ChargeTypeResource\Pages;

use App\Filament\Resources\ChargeTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChargeTypes extends ListRecords
{
    protected static string $resource = ChargeTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New type')
                ->icon('heroicon-s-plus-circle'),
        ];
    }
}
