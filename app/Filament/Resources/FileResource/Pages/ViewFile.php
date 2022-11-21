<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Pages\InvoicesReport;
use App\Filament\Resources\FileResource;
use App\Models\File;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make()
                ->icon('heroicon-s-pencil'),
            ActionGroup::make([
                DeleteAction::make(),
            ]),
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }
}
