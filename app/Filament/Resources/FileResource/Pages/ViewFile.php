<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make()
                ->icon('heroicon-s-pencil'),
            // \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make(),
        ];
    }
}
