<?php

namespace App\Filament\Resources\ServiceProviderResource\Pages;

use App\Filament\Resources\ServiceProviderResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceProviders extends ManageRecords
{
    protected static string $resource = ServiceProviderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
