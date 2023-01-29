<?php

namespace App\Filament\Pages;

use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Insurance;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class InsurancesReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.insurances-reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Insurances';

    public $insurance_id;

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
        'insurance_id' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder
    {
        if ($this->insurance_id) {
            return Invoice::query()
                ->whereRelation('session', function (Builder $query) {
                    return $query->whereRelation('fileInsurance', 'insurance_id', $this->insurance_id);
                });
        } else {
            return Insurance::query();
        }
    }

    protected function getTableColumns(): array
    {
        $columns = [];
        if ($this->insurance_id) {
            if ($this->insurance_id == 4) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('specific_data.voucher_number')
                        ->label('Voucher Identification')
                        ->sortable()
                        ->formatStateUsing(fn ($state, $record) => "40440006/" . $state . "/" . substr($record->session->date, 2, 2)),
                    TextColumn::make('session.fileInsurance.specific_data.member_number')
                        ->label("Beneficiary's Affiliation No")
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
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliate_name')
                        ->label("Affiliate's Names")
                        // ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliate_affectation')
                        ->label("Affiliate's Affectation")
                        // ->searchable()
                        ->sortable(),
                    TextColumn::make('id')
                        ->label("Cost For Consultation 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1)
                                        ->whereRaw('name NOT REGEXP "HOSPITAL VISIT"');
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
                                        return $query->whereNotIn('charge_type_id', [1, 2, 28, 5, 3]);
                                    })
                                        ->orWhereRaw('name REGEXP "HOSPITAL VISIT"');
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
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 6 || $this->insurance_id == 9) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.police_number')
                        ->label('No Police')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliation_number')
                        ->label('No Carte')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.names')
                        ->label('Nom et Prenom Du Malade')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('id')
                        ->label("Cons. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Examen Comp. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.id')
                        ->label("Hosp. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Acte Et Mater 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idd')
                        ->label("Medic 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.iddd')
                        ->label("Total 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idddd')
                        ->label("A Payer Par SORAS")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 7) {
                $columns = [
                    TextColumn::make('session.fileInsurance.file.specific_data.scheme_name')
                        ->label('Scheme Name')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.police_number')
                        ->label('Smart Card Number')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.invoice_number')
                        ->label('Claim Form Number/Invoice Number')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.specific_data.last_name')
                        ->label('Last Name')
                        ->formatStateUsing(fn (Invoice $record) => $record->session->fileInsurance->file->specific_data['last_name'] ?? $record->session->fileInsurance->file->names)
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('session.fileInsurance.file.specific_data.first_name')
                        ->label('First Name')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('session.date')
                        ->label('Treatment Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('id')
                        ->label("Consultation")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Lab Tests")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idd')
                        ->label("Drugs")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.id')
                        ->label("Imaging")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 28);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Procedures")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 28, 4, 5]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.id')
                        ->label("Consumables")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 4);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.idsasa')
                        ->label("Bed Charges")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.id')
                        ->label("Benefit Type(OP, IP, MAT)")
                        ->getStateUsing(function (Invoice $record) {
                            $hospitalizationCost = $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                            return $hospitalizationCost > 0 ? 'IPD' : 'OPD';
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.iddd')
                        ->label("Gross Amount")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.iddddd')
                        ->label("Copay")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->insured_pays != 100 ? $record?->discount->insured_pays / 100 : $record?->discount->insured_pays));
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idddd')
                        ->label("Payable by BRITAM")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 8) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliate_name')
                        ->label('Assure Principal')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.names')
                        ->label('Nom du Malade')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('id')
                        ->label("Consultation")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Labo")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.idsasa')
                        ->label("Hospitalisation")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Actes + Med")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.iddd')
                        ->label("Total Amount")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idddd')
                        ->label("Total a Payer")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 11) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.member_number')
                        ->label('Member No')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.names')
                        ->label('Full Name of Patient')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('id')
                        ->label("Cost of Consultation 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Cost of Laboratory Tests 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.id')
                        ->label("Cost of Hospitalization 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Cost of Procedures And Material 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idd')
                        ->label("Cost of Drugs(Medicaments) 100%")
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
                        ->label("Total Amount")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 12) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliation_number')
                        ->label('No Carte')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.names')
                        ->label('Nom et Prenom Du Malade')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('id')
                        ->label("Cons. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Examen Comp. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.id')
                        ->label("Hosp. 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Acte Et Mater 100%")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idd')
                        ->label("Medic 100%")
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
                        ->label("A Payer Par RADIANT")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            } else if ($this->insurance_id == 13 || $this->insurance_id == 14 || $this->insurance_id == 15) {
                $columns = [
                    TextColumn::make('session.date')
                        ->label('Date')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.specific_data.affiliation_number')
                        ->label('Member Number')
                        ->sortable(),
                    TextColumn::make('session.fileInsurance.file.names')
                        ->label('Full Name of Patient')
                        ->sortable()
                        ->wrap(),
                    TextColumn::make('id')
                        ->label("Cost of Consultation")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.idf')
                        ->label("Cost of Laboratory Tests")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.file.id')
                        ->label("Cost of Hospitalization")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.id')
                        ->label("Cost of Procedures And Material")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                                    });
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idd')
                        ->label("Cost of Drugs(Medicaments)")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->whereRelation('charge', function (Builder $query) {
                                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                                })
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.iddd')
                        ->label("Total Amount")
                        ->getStateUsing(function (Invoice $record) {
                            return $record->charges()
                                ->sum('total_price');
                        }),
                    TextColumn::make('session.fileInsurance.specific_data.idddd')
                        ->label("Total Amount")
                        ->getStateUsing(function (Invoice $record) {
                            return round($record->charges()->sum('total_price') * ($record?->discount->discount > 0 ? $record?->discount->discount / 100 : $record?->discount->discount));
                        }),
                ];
            }
        } else {
            $columns = [
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ];
        }

        return $columns;
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make(),
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        if ($this->insurance_id) {
            return fn (Model $record): string => InvoiceResource::getUrl('view', $record->id);
        } else {
            return fn (Model $record): string => $this->getUrl([
                "tableFilters" => array_merge($this->tableFilters),
                "insurance_id" => $record->id
            ]);
        }
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
                    if ($this->insurance_id) {
                        return $query
                            ->when(
                                $data['since'],
                                fn (Builder $query, $date): Builder => $query->whereRelation('session', 'date', '>=', Carbon::parse($date)->format('Y-m-d')),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereRelation('session', 'date', '<=', Carbon::parse($date)->format('Y-m-d')),
                            );
                    } else {
                        return $query
                            ->when(
                                $data['since'],
                                fn (Builder $query, $date): Builder => $query->whereRelation('sessions', 'date', '>=', Carbon::parse($date)->format('Y-m-d')),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereRelation('sessions', 'date', '<=', Carbon::parse($date)->format('Y-m-d')),
                            );
                    }
                }),
        ];
    }

    public function export()
    {
        if ($this->insurance_id) {
            return Excel::download(new InvoicesExport($this->tableFilters, $this->insurance_id), 'invoices.xlsx');    
        }
        return Excel::download(new InvoicesExport($this->tableFilters), 'invoices.xlsx');
    }

    public function exportIPD()
    {
        return Excel::download(new InvoicesExport($this->tableFilters, 5, "IPD"), 'invoices.xlsx');
    }

    protected function getTableActions(): array
    {
        $actions = [];
        if (!$this->insurance_id) {
            $actions[] = ActionGroup::make([
                Action::make('Export OPD')
                    ->label('Export OPD')
                    ->action('export')
                    ->icon('heroicon-s-download')
                    ->hidden(fn (Model $record) => $record?->id !== 5),
                Action::make('Export IPD')
                    ->label('Export IPD')
                    ->action('exportIPD')
                    ->icon('heroicon-s-download')
                    ->hidden(fn (Model $record) => $record?->id !== 5)
            ]);
        }
        return $actions;
    }

    public function getActions(): array
    {
        return [
            Pages\Actions\Action::make('Export')
                ->label('Export')
                ->action(function () {
                    return $this->export();
                })
                ->icon('heroicon-s-download')
                ->hidden(fn () => $this->insurance_id !== 4),
        ];
    }
}
