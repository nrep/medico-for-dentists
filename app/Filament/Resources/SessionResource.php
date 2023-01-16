<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages\CreateInvoice;
use App\Filament\Resources\SessionResource\Pages;
use App\Filament\Resources\SessionResource\RelationManagers;
use App\Models\File;
use App\Models\FileInsurance;
use App\Models\Insurance;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessionResource extends Resource
{
    protected static ?string $model = Session::class;

    // protected static ?string $navigationIcon = 'gmdi-edit-document';

    protected static ?string $modelLabel = 'reception';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->required()
                    ->maxDate(Carbon::now())
                    ->default(Carbon::now()),
                Select::make('file_insurance_id')
                    ->label('Insurance')
                    ->options(function ($record, Closure $get) {
                        if ($record) {
                            return $record->fileInsurance->file->linkedInsurances->pluck('insurance.name', 'id');
                        }
                        return [];
                    })
                    ->searchable()
                    ->required()
                    ->reactive(),
                /* Select::make('discount_id')
                    ->label('Percentage to be paid (T.M)')
                    ->options(function (callable $get) {
                        if ($get('file_insurance_id')) {
                            return FileInsurance::find($get('file_insurance_id'))
                                ->insurance
                                ->discounts()
                                ->pluck('display_name', 'id');
                        }
                        return [];
                    })
                    ->required(true),
                TextInput::make('specific_data.voucher_number')
                    ->prefix("40440006/")
                    ->suffix("/" . date('y'))
                    ->hidden(function (Closure $get) {
                        $bool = true;
                        if ($get('file_insurance_id')) {
                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                            if ($insurance->id == 4) {
                                $bool = false;
                            }
                        }
                        return $bool;
                    })
                    ->required(function (Closure $get) {
                        $bool = false;
                        if ($get('file_insurance_id')) {
                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                            if ($insurance->id == 4) {
                                $bool = true;
                            }
                        }
                        return $bool;
                    }) */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fileInsurance.file.number')
                    ->formatStateUsing(fn (Session $record): string => sprintf("%04d", substr($record->fileInsurance->file->number, 0)) . "/" . $record->fileInsurance->file->registration_year)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fileInsurance.file.names')
                    ->formatStateUsing(fn (Session $record): string => $record->fileInsurance->file->names)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fileInsurance.insurance.name')
                    ->formatStateUsing(fn (Session $record): string => $record->fileInsurance->insurance->name)
                    ->searchable()
                    ->sortable()
                // Tables\Columns\TagsColumn::make('specific_data'),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->default(now()),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }
    
                        return 'Received on ' . Carbon::parse($data['date'])->toFormattedDateString();
                    })
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
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['insurance_id']) {
                            return null;
                        }
    
                        return 'Insurance: ' . Insurance::find($data['insurance_id'])?->name;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['insurance_id']) ? $query->whereRelation('fileInsurance', 'insurance_id', $data['insurance_id']) : $query;
                    }),
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Action::make('invoice')
                    ->icon(function (Session $record) {
                        $icon = 'heroicon-o-document';
                        if ($record->invoice) {
                            $icon = 'heroicon-o-eye';
                        } else {
                            $icon = 'heroicon-o-plus';
                        }
                        return $icon;
                    })
                    ->label(function (Session $record) {
                        $label = "";
                        if ($record->invoice) {
                            $label = "View invoice";
                        } else {
                            $label = "Create invoice";
                        }
                        return $label;
                    })
                    ->url(function (Session $record) {
                        if ($record->invoice) {
                            return InvoiceResource::getUrl('view', ['record' => $record->invoice]);
                        } else {
                            return InvoiceResource::getUrl('create', ['session' => $record]);
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->action(function (array $data, $record): void {
                        if ($record) {
                            $record->update($data);
                        }
                    })
                    ->form(function (Session $record) {
                        return [
                            Block::make('')
                                ->schema([
                                    DatePicker::make('date')
                                        ->required()
                                        ->maxDate(Carbon::now())
                                        ->default(Carbon::now()),
                                    Select::make('file_insurance_id')
                                        ->label('Insurance')
                                        ->options($record->fileInsurance->file->linkedInsurances->pluck('insurance.name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->default($record->fileInsurance->file->linkedInsurances->first()->id)
                                        ->reactive(),
                                    /* Select::make('discount_id')
                                        ->label('Percentage to be paid (T.M)')
                                        ->options(function (callable $get) {
                                            if ($get('file_insurance_id')) {
                                                return FileInsurance::find($get('file_insurance_id'))
                                                    ->insurance
                                                    ->discounts()
                                                    ->pluck('display_name', 'id');
                                            }
                                            return [];
                                        })
                                        ->default($record->fileInsurance->file->linkedInsurances->first()->insurance->discounts()->first()->id)
                                        ->required(true)
                                        ->searchable(),
                                    TextInput::make('specific_data.voucher_number')
                                        ->prefix("40440006/")
                                        ->suffix("/" . date('y'))
                                        ->hidden(function (Closure $get) {
                                            $bool = true;
                                            if ($get('file_insurance_id')) {
                                                $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                                if ($insurance->id == 4) {
                                                    $bool = false;
                                                }
                                            }
                                            return $bool;
                                        })
                                        ->required(function (Closure $get) {
                                            $bool = false;
                                            if ($get('file_insurance_id')) {
                                                $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                                if ($insurance->id == 4) {
                                                    $bool = true;
                                                }
                                            }
                                            return $bool;
                                        }) */
                                ])
                                ->columns(2),
                        ];
                    }),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSessions::route('/'),
            'create' => Pages\CreateSession::route('/create'),
            // 'edit' => Pages\EditSession::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
