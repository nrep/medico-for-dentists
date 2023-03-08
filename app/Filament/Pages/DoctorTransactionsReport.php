<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\InvoiceDay;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DoctorTransactionsReport extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'fontisto-doctor';

    protected static string $view = 'filament.pages.doctor-transactions-report';

    protected static ?string $navigationLabel = 'Doctors';

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
        $query = Employee::select(
            'employees.names',
            'invoice_days.doctor_id',
            DB::raw("COUNT(invoice_days.id) AS total"),
        )
            ->join('invoice_days', 'employees.id', 'invoice_days.doctor_id')
            ->where('invoice_days.number', 1)
            ->groupBy(['employees.names', 'invoice_days.doctor_id']);
        // $query = Invoice::select(
        //     'invoice_days.doctor_id',
        //     'employees.names',
        //     DB::raw("COUNT(invoice_days.id) AS total"),
        //     DB::raw('SUM(invoice_items.total_price) AS total_amount')
        // )
        //     ->join('invoice_days', 'invoices.id', 'invoice_days.invoice_id')
        //     ->join('employees', 'invoice_days.doctor_id', 'employees.id')
        //     ->join('invoice_items', 'invoice_days.id', 'invoice_items.invoice_day_id')
        //     ->where('invoice_days.number', 1)
        //     ->groupBy(['employees.names', 'invoice_days.doctor_id']);
        return $query;
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('names')
                ->label('Doctor')
                ->searchable()
                ->sortable(),
            TextColumn::make('total')
                ->label('Patients count')
                ->getStateUsing(function (Employee $record) {
                    return InvoiceDay::where('doctor_id', $record->doctor_id)
                        ->where('number', 1)
                        ->whereRelation('invoice', function (Builder $query) {
                            return $query->whereRelation('session', function (Builder $query) {
                                return $query->where('date', '>=', Carbon::parse($this->tableFilters['Since']['since'])->format('Y-m-d'))
                                    ->where('date', '<=', Carbon::parse($this->tableFilters['Until']['until'])->format('Y-m-d'));
                            });
                        })
                        ->count();
                })
                ->searchable()
                ->sortable(),
            TextColumn::make('total_amount')
                ->getStateUsing(function (Employee $record) {
                    $query = InvoiceItem::whereRelation('day', function (Builder $query) use ($record) {
                        return $query->where('doctor_id', $record->doctor_id)
                            ->where('number', 1)
                            ->whereRelation('invoice', function (Builder $query) {
                                return $query->whereRelation('session', function (Builder $query) {
                                    return $query->where('date', '>=', Carbon::parse($this->tableFilters['Since']['since'])->format('Y-m-d'))
                                        ->where('date', '<=', Carbon::parse($this->tableFilters['Until']['until'])->format('Y-m-d'));
                                });
                            });
                    })
                        ->sum('total_price');

                    return $query;
                })
                ->label('Total amount')
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->searchable()
                ->sortable(),
        ];

        return $columns;
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->names;
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
            Filter::make('Since')
                ->form([
                    DatePicker::make('since')
                        ->default(now()),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['since']) {
                        return null;
                    }

                    return 'Received since ' . Carbon::parse($data['since'])->toFormattedDateString();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['since'])->format('Y-m-d');
                    return $query->whereRelation('days', fn (Builder $query) => $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', '>=', $data['date'])));
                }),
            Filter::make('Until')
                ->form([
                    DatePicker::make('until')
                        ->default(now()),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['until']) {
                        return null;
                    }

                    return 'Until ' . Carbon::parse($data['until'])->toFormattedDateString();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['until'])->format('Y-m-d');
                    return $query->whereRelation('days', fn (Builder $query) => $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', '<=', $data['date'])));
                }),
            Filter::make('Insurance')
                ->form([
                    Select::make('insurance_id')
                        ->label("Insurance")
                        ->options(Insurance::all()->pluck('name', 'id'))
                        ->searchable(),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['insurance_id']) {
                        return null;
                    }

                    return 'Insurance: ' . Insurance::find($data['insurance_id'])?->name;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['insurance_id'])) {
                        $query->whereRelation('session', fn (Builder $query) => $query->whereRelation('discount', 'insurance_id', $data['insurance_id']));
                    }
                    return $query;
                })
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => InvoicesReport::getUrl([
            "tableFilters" => array_merge($this->tableFilters, [
                "doctor" => [
                    "doctor_id" => $record->doctor_id
                ]
            ])
        ]);
    }
}
