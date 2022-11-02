<?php

namespace App\Filament\Pages;

use App\Models\Insurance;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserSessionsReport extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'tabler-report';

    protected static string $view = 'filament.pages.user-sessions-report';

    protected static ?string $navigationLabel = 'Receptions';

    protected static ?string $navigationGroup = 'Reports';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder
    {
        $query = Session::select(
            'done_by',
            'users.name',
            DB::raw("COUNT(*) AS total")
        )
            ->join('users', 'done_by', 'users.id')
            ->groupBy(['done_by', 'users.name']);
        return $query;
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('name')
                ->label('User name')
                ->searchable()
                ->sortable(),
            TextColumn::make('total')
                ->label('Sessions count')
                ->searchable()
                ->sortable(),
        ];

        return $columns;
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->done_by;
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
        ];
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
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => SessionsReport::getUrl([
            "tableFilters" => array_merge($this->tableFilters, [
                "done_by" => [
                    "done_by" => $record->done_by
                ]
            ])
        ]);
    }
}
