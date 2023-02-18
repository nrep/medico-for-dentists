<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use App\Models\File;
use App\Models\FileInsurance;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

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
