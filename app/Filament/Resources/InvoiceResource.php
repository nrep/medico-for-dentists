<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\Pages\ViewInvoice;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Resources\InvoiceResource\RelationManagers\DaysRelationManager;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceInsuranceTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoicePatientTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceTotalPrice;
use App\Models\Charge;
use App\Models\Employee;
use App\Models\EmployeeEmployeeCategory;
use App\Models\FileInsurance;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Webbingbrasil\FilamentDateFilter\DateFilter;
use Stevebauman\Purify\Facades\Purify;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    // protected static ?string $navigationIcon = 'iconpark-order-o';

    protected static ?string $modelLabel = 'bill';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 5;

    public $session;

    protected $queryString = ['session'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('discount_id')
                            ->label('Percentage to be paid (T.M)')
                            ->options(function ($record, $livewire) {
                                if ($livewire->session) {
                                    return $livewire->session->fileInsurance->insurance
                                        ->discounts()
                                        ->pluck('display_name', 'id');
                                } else if ($record->session) {
                                    return $record->session->fileInsurance->insurance
                                        ->discounts()
                                        ->pluck('display_name', 'id');
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(true),
                        TextInput::make('specific_data.voucher_number')
                            ->prefix("40440006/")
                            ->suffix("/" . date('y'))
                            ->hidden(function ($record, $livewire) {
                                $bool = true;
                                if ($livewire->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 4) {
                                        $bool = false;
                                    }
                                } else if ($record->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 4) {
                                        $bool = false;
                                    }
                                }
                                return $bool;
                            })
                            ->required(function ($record, $livewire) {
                                $bool = false;
                                if ($livewire->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 4) {
                                        $bool = true;
                                    }
                                } else if ($record->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 4) {
                                        $bool = true;
                                    }
                                }
                                return $bool;
                            }),
                        TextInput::make('specific_data.invoice_number')
                            ->hidden(function ($record, $livewire) {
                                $bool = true;
                                if ($livewire->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 7) {
                                        $bool = false;
                                    }
                                } else if ($record->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 7) {
                                        $bool = false;
                                    }
                                }
                                return $bool;
                            })
                            ->required(function ($record, $livewire) {
                                $bool = false;
                                if ($livewire->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 7) {
                                        $bool = true;
                                    }
                                } else if ($record->session?->file_insurance_id) {
                                    $insurance = FileInsurance::find($livewire->session->file_insurance_id)->insurance;
                                    if ($insurance->id == 7) {
                                        $bool = true;
                                    }
                                }
                                return $bool;
                            }),
                        InvoiceResource::getSessionIdFormField(),
                        InvoiceResource::getNumberOfDaysFormField(),
                        Repeater::make('days')
                            ->relationship()
                            ->schema(InvoiceResource::getDaysSchema())
                            ->columnSpan(2)
                            ->columns(2)
                            ->collapsible()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Invoice number')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "PROV-" . sprintf("%06d", $state)),
                TextColumn::make('session.fileInsurance.file.number')
                    ->formatStateUsing(fn (Invoice $record) => sprintf("%05d", $record->session->fileInsurance->file->number) . "/" . $record->session->fileInsurance->file->registration_year)
                    ->label('File number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session.fileInsurance.file.names')
                    ->label('Patient Names')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('session.fileInsurance.insurance.name')
                    ->searchable()
                    ->sortable()
                // BooleanColumn::make('payments')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->default(now()),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }

                        return 'Billed on ' . Carbon::parse($data['date'])->toFormattedDateString();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                        return $query->whereRelation('session', 'date', $data['date']);
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
                        return isset($data['insurance_id']) ? $query->whereRelation('session', fn (Builder $query) => $query->whereRelation('fileInsurance', 'insurance_id', $data['insurance_id'])) : $query;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            DaysRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getSessionIdFormField(): Hidden
    {
        return Hidden::make("session_id")
            ->required()
            ->default(request('session'));
    }

    public static function getNumberOfDaysFormField(): Hidden
    {
        return Hidden::make("number-of-days")
            ->default(request('days'));
    }

    public static function getCurrentDayFormField($stepIndex): TextInput
    {
        return TextInput::make("current_day")
            ->label('Current Day')
            ->default($stepIndex + 1)
            ->reactive();
    }

    public static function getChargeItemTotalPrice($unitPrice, $quantity)
    {
        return $unitPrice * $quantity;
    }

    public static function getWidgets(): array
    {
        return [
            InvoiceTotalPrice::class,
            InvoiceInsuranceTotalPrice::class,
            InvoicePatientTotalPrice::class
        ];
    }

    public static function getCleanOptionString(Model $model): string
    {
        return Purify::clean(
            view('filament.components.select-user-result')
                ->with('name', $model?->name)
                ->with('price', $model?->price)
                ->with('type', $model?->chargeListChargeType?->chargeType?->name)
                ->render()
        );
    }

    public static function getDaysSchema(): array
    {
        return [
            DatePicker::make("date")
                ->label('Date')
                ->required()
                ->default(date('Y-m-d')),
            Select::make('doctor_id')
                ->label('Doctor')
                ->options(function () {
                    return Employee::whereHas('categories', function (Builder $query) {
                        $query->where('employee_category_id', 1);
                    })
                        ->pluck('names', 'id');
                })
                ->required()
                ->searchable(),
            Repeater::make('items')
                ->relationship()
                ->schema([
                    Hidden::make('done_by')
                        ->default(auth()->user()->id),
                    Select::make('charge_id')
                        ->label('Charge')
                        ->options(function ($livewire) {
                            return Charge::whereRelation('chargeListChargeType', function (Builder $query) use ($livewire) {
                                return $query->whereRelation('chargeList', function (Builder $query) use ($livewire) {
                                    return $query->whereRelation('linkedInsurances', function (Builder $query) use ($livewire) {
                                        return $query->whereRelation('insurance', 'id', $livewire?->session?->fileInsurance->insurance_id);
                                    });
                                });
                            })
                                ->pluck('name', 'id');
                        })
                        ->allowHtml()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return Charge::whereRelation('chargeListChargeType', function (Builder $query) {
                                return $query->whereRelation('chargeList', function (Builder $query) {
                                    return $query->whereRelation('linkedInsurances', function (Builder $query) {
                                        $insuranceId = Session::find(request()->json()->get('serverMemo')['data']['data']['session_id'])->fileInsurance->insurance_id;
                                        if ($insuranceId) {
                                            return $query->whereRelation('insurance', 'id', $insuranceId);
                                        } else {
                                            return $query;
                                        }
                                    });
                                });
                            })
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('price', 'like', "%{$search}%")
                                ->pluck('name', 'id');
                        })
                        ->getOptionLabelUsing(function ($component, $value): string {
                            $charge = Charge::find($value);

                            return $charge->name . ' - ' . $charge->price;
                        })
                        ->reactive()
                        ->afterStateUpdated(function (Closure $get, Closure $set, $state, $context, $record) {
                            $charge = Charge::find($state);
                            if ($charge) {
                                $set('sold_at', $charge->price);
                                $set('unit_price', $charge->price);
                                $set('quantity', 1);
                                $set('total_price', $charge->price * 1);
                            } else {
                                $set('sold_at', '');
                                $set('unit_price', '');
                                $set('quantity', '');
                                $set('total_price', '');
                            }
                        })
                        ->columnSpan(3)
                        ->required(),
                    TextInput::make('sold_at')
                        ->numeric()
                        ->extraInputAttributes(["readonly" => "true"]),
                    TextInput::make('quantity')
                        ->numeric()
                        ->reactive()
                        ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                            $set('total_price', $get('sold_at') * $state);
                        })
                        ->required(),
                    TextInput::make('total_price')
                        ->numeric()
                        ->disabled()
                ])
                ->columns(6)
                ->columnSpan(2)
                ->collapsible()
        ];
    }
}
