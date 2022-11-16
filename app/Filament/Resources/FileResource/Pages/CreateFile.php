<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['full_number'] = sprintf("%05d", $data['number']) . "/" . $data['registration_year'];
        
        return $data;
    }

    protected function onValidationError(ValidationException $exception): void
    {
        $message = $exception->getMessage();
        if (strstr(array_keys($exception->validator->failed())[0], 'full_number')) {
            $message = 'File number exists in the system. Please review and try again.';
        }
        Notification::make()
            ->title($message)
            ->danger()
            ->send();
    }
}
