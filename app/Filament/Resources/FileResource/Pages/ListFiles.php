<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Resources\FileResource;
use App\Models\Member;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFiles extends ListRecords
{
    protected static string $resource = FileResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-s-plus-circle'),
        ];
    }

    /* protected function getTableQuery(): Builder
    {
        return Member::query();
    } */
}
