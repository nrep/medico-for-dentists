<?php

namespace App\Filament\Resources\ChargeResource\Pages;

use App\Filament\Resources\ChargeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCharges extends ListRecords
{
    protected static string $resource = ChargeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-plus-circle'),
        ];
    }
}
