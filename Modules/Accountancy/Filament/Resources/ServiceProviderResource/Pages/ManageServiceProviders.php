<?php

namespace Modules\Accountancy\Filament\Resources\ServiceProviderResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Accountancy\Filament\Resources\ServiceProviderResource;

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
