<?php

namespace App\Filament\Pages;

use App\Models\Insurance;
use App\Models\Session;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class SessionsReport extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'tabler-report';

    protected static string $view = 'filament.pages.sessions-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Receptions';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('Receptionist') && !auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder
    {
        return Session::query();
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('id')
                ->label('Invoice number')
                ->searchable()
                ->sortable()
                ->formatStateUsing(fn ($state) => "PROV-" . sprintf("%06d", $state)),
            TextColumn::make('fileInsurance.file.number')
                ->formatStateUsing(fn (Session $record) => sprintf("%04d", $record->fileInsurance->file->number) . "/" . $record->fileInsurance->file->registration_year)
                ->label('File number')
                ->searchable()
                ->sortable(),
            TextColumn::make('fileInsurance.file.names')
                ->label('Patient Names')
                ->searchable()
                ->sortable(),
            TextColumn::make('fileInsurance.insurance.name')
                ->searchable()
                ->sortable(),
            TextColumn::make('created_at')
                ->date()
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans(Carbon::now(), CarbonInterface::DIFF_RELATIVE_TO_NOW)),
        ];

        return $columns;
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('date')
                ->form([
                    DatePicker::make('date')
                        ->default(now()),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                    return $query->where('date', $data['date']);
                }),
            Filter::make('Insurance')
                ->form([
                    Select::make('insurance_id')
                        ->label("Insurance")
                        ->options(Insurance::all()->pluck('name', 'id'))
                        ->searchable(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['insurance_id'])) {
                        $query->whereRelation('discount', 'insurance_id', $data['insurance_id']);
                    }
                    return $query;
                }),
            Filter::make('done_by')
                ->form([
                    Select::make('done_by')
                        ->label("Done By")
                        ->options(User::whereRelation('roles', 'name', 'Receptionist')->pluck('name', 'id'))
                        ->searchable()
                        ->default(auth()->user()->hasRole("Receptionist") ? auth()->id() : null),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['done_by'])) {
                        $query->where('done_by', $data['done_by']);
                    }
                    return $query;
                })
                ->hidden(!auth()->user()->hasAnyRole(["Admin", "Data Manager"])),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
        ];
    }
}
