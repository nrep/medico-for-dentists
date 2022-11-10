<?php

namespace App\Filament\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class InsurancesReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.insurances-reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Insurances';

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
        return Invoice::query()
            ->whereRelation('session', function (Builder $query) {
                return $query->whereRelation('fileInsurance', 'insurance_id', 4);
            });
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('session.date')
                ->label('Date')
                ->searchable()
                ->sortable(),
            TextColumn::make('session.specific_data.voucher_number')
                ->label('Voucher Identification')
                ->searchable()
                ->sortable()
                ->formatStateUsing(fn ($state) => "40440006/" . $state . "/" . date('y')),
            TextColumn::make('session.fileInsurance.specific_data.member_number')
                ->label("Beneficiary's Affiliation No")
                ->searchable()
                ->sortable(),
            TextColumn::make('session.fileInsurance.file.year_of_birth')
                ->label("Beneficiary's Age")
                ->searchable()
                ->sortable(),
            TextColumn::make('session.fileInsurance.file.sex')
                ->label("Beneficiary's Sex")
                ->searchable()
                ->sortable(),
            TextColumn::make('session.fileInsurance.file.names')
                ->label("Beneficiary's Names")
                ->searchable()
                ->sortable(),
            TextColumn::make('session.fileInsurance.specific_data.affiliate_name')
                ->label("Affiliate's Names")
                ->searchable()
                ->sortable(),
            TextColumn::make('session.fileInsurance.specific_data.affiliate_affectation')
                ->label("Affiliate's Affectation")
                ->searchable()
                ->sortable(),
            TextColumn::make('id')
                ->label("Cost For Consultation 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.id')
                ->label("Cost For Laboratory Tests 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.id')
                ->label("Cost For Medical Imaging 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 28);
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.file.id')
                ->label("Cost For Hospitalization 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.specific_data.id')
                ->label("Cost For Procedures & Materials 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                return $query->whereNotIn('charge_type_id', [1, 2, 28, 5]);
                            });
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.specific_data.idd')
                ->label("Cost For Medicines 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->whereRelation('charge', function (Builder $query) {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                        })
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.specific_data.iddd')
                ->label("Total Amount 100%")
                ->getStateUsing(function (Invoice $record) {
                    return $record->charges()
                        ->sum('total_price');
                }),
            TextColumn::make('session.fileInsurance.specific_data.idddd')
                ->label("Total Amount 85%")
                ->getStateUsing(function (Invoice $record) {
                    return round($record->charges()->sum('total_price') * (85 / 100));
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make(),
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => InvoiceResource::getUrl('view', $record->id);
    }

    protected function getTableFilters(): array
    {
        $periods = [
            'today' => 'Today',
            'weekly' => 'This Week',
            'monthly' => 'This Month',
            'custom' => 'Custom'
        ];

        return [
            Filter::make('period')
                ->form([
                    Select::make('period')
                        ->options($periods)
                        ->default('monthly')
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (Closure $get, Closure $set, $state, $context, $record) {
                            switch ($state) {
                                case 'today':
                                    $set('since', date('Y-m-d'));
                                    $set('until', date('Y-m-d'));
                                    break;
                                case 'weekly':
                                    $set('since', Carbon::parse(date('Y-m-d'))->startOfWeek(1));
                                    $set('until', Carbon::parse(date('Y-m-d'))->endOfWeek(1));
                                    break;
                                case 'monthly':
                                    $set('since', Carbon::parse(date('Y-m-d'))->startOfMonth());
                                    $set('until', Carbon::parse(date('Y-m-d'))->endOfMonth()->subDay());
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                        }),
                    DatePicker::make('since')
                        ->default(Carbon::parse(date('Y-m-d'))->startOfMonth()),
                    DatePicker::make('until')
                        ->default(Carbon::parse(date('Y-m-d'))->endOfMonth()),
                ])
                ->indicateUsing(function (array $data) use ($periods): array {
                    $indicators = [];

                    if ($data['period'] ?? null) {
                        $indicators['period'] = 'Period: ' . $periods[$data['period']];
                    }

                    if ($data['since'] ?? null) {
                        $indicators['since'] = 'Created since ' . Carbon::parse($data['since'])->toFormattedDateString();
                    }

                    if ($data['until'] ?? null) {
                        $indicators['until'] = 'Until ' . Carbon::parse($data['until'])->toFormattedDateString();
                    }

                    return $indicators;
                })
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['since'],
                            fn (Builder $query, $date): Builder => $query->whereRelation('session', 'date', '>=', Carbon::parse($date)->format('Y-m-d')),
                        )
                        ->when(
                            $data['until'],
                            fn (Builder $query, $date): Builder => $query->whereRelation('session', 'date', '<=', Carbon::parse($date)->format('Y-m-d')),
                        );
                }),
        ];
    }
}
