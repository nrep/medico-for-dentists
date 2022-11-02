<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\Session;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationGroup = 'Reports';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        if (auth()->user()->hasRole('Cashier')) {
            $query = Invoice::query();
        } else if (auth()->user()->hasRole('Receptionist')) {
            $query = Session::query()->where('done_by', auth()->user()->id);
        } else {
            $query = Invoice::query();
        }
        return $query;
    }

    protected function getTableColumns(): array
    {
        $columns = [];

        if (auth()->user()->hasRole('Cashier')) {
            $columns = [
                TextColumn::make('session_id')
                    ->label('Invoice number')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "PROV-" . sprintf("%06d", $state)),
                TextColumn::make('session.fileInsurance.file.number')
                    ->formatStateUsing(fn (Invoice $record) => sprintf("%04d", $record->session->fileInsurance->file->number) . "/" . $record->session->fileInsurance->file->registration_year)
                    ->label('File number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session.fileInsurance.file.names')
                    ->label('Patient Names')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session.fileInsurance.insurance.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(function (Invoice $record) {
                        $totalAmount = 0;

                        foreach ($record->charges as $key => $charge) {
                            $totalAmount += $charge->totalPrice;
                        }

                        return $totalAmount;
                    })
                    ->searchable(),
                TextColumn::make('insurance_pays')
                    ->label('Insurance')
                    ->formatStateUsing(function (Invoice $record) {
                        $totalAmount = $insuracePays = 0;

                        foreach ($record->charges as $key => $charge) {
                            $totalAmount += $charge->totalPrice;
                        }

                        if ($totalAmount > 0) {
                            $insuracePays = $totalAmount * $record->session->discount->discount / 100;
                        }

                        return round($insuracePays);
                    })
                    ->searchable(),
                TextColumn::make('patient_pays')
                    ->label('Patient')
                    ->formatStateUsing(function (Invoice $record) {
                        $totalAmount = $patientPays = 0;

                        foreach ($record->charges as $key => $charge) {
                            $totalAmount += $charge->totalPrice;
                        }

                        if ($totalAmount > 0) {
                            $patientPays = $totalAmount * (100 - $record->session->discount->discount) / 100;
                        }

                        return round($patientPays);
                    })
                    ->searchable(),
                TextColumn::make('paid')
                    ->label('Paid')
                    ->formatStateUsing(function (Invoice $record) {
                        $paid = 0;

                        foreach ($record->payments as $key => $payment) {
                            $paid += $payment->amount;
                        }

                        return $paid;
                    })
                    ->searchable()
            ];
        } else if (auth()->user()->hasRole('Receptionist')) {
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
        }

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
                    if (auth()->user()->hasRole('Cashier')) {
                        $query->whereRelation('session', 'date', $data['date']);
                    } else if (auth()->user()->hasRole('Receptionist')) {
                        $query->where('date', $data['date']);
                    }
                    return $query;
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
                        if (auth()->user()->hasRole('Cashier')) {
                            $query->whereRelation('session', fn (Builder $query) => $query->whereRelation('discount', 'insurance_id', $data['insurance_id']));
                        } else if (auth()->user()->hasRole('Receptionist')) {
                            $query->whereRelation('discount', 'insurance_id', $data['insurance_id']);
                        }
                    }
                    return $query;
                })
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => InvoiceResource::getUrl('view', $record->id);
    }
}
