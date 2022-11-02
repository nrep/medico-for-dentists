<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $fileNumber = $data["number"];
        $data['number'] = substr($fileNumber, 0, 4);
        $data['registration_year'] = substr($fileNumber, 4);
        
        return $data;
    }
}
