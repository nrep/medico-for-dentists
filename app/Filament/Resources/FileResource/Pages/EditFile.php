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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['full_number'] = sprintf("%05d", $data['number']) . "/" . $data['registration_year'];
        
        return $data;
    }
}
