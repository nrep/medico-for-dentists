<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFile extends EditRecord
{
    protected static string $resource = FileResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['number'] = sprintf("%04d", $data['number']);
        $data['number'] .= $data['registration_year'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $fileNumber = $data["number"];
        $data['number'] = substr($fileNumber, 0, 4);
        $data['registration_year'] = substr($fileNumber, 4);
        $data['full_number'] = $data['number'] . "/" . $data['registration_year'];
        
        return $data;
    }
}
