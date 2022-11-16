<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Forms\Components\TextInputWithAddons;
use App\Models\Cell;
use App\Models\District;
use App\Models\File;
use App\Models\FileInsurance;
use App\Models\Insurance;
use App\Models\Province;
use App\Models\Sector;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInput;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Model;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    // protected static ?string $navigationIcon = 'tabler-file';

    protected static ?string $recordTitleAttribute = 'names';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'names', 'phone_number', 'emergencyContacts.name', 'emergencyContacts.phone_number'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('number')
                            ->required()
                            ->autofocus()
                            ->maxLength(5)
                            ->minLength(1)
                            ->numeric()
                            ->reactive()
                            ->disableAutocomplete()
                            ->suffix("/")
                            ->mask(fn (TextInput\Mask $mask) => $mask->pattern('00000')),
                        TextInput::make('registration_year')
                            ->label('Year')
                            ->numeric()
                            ->required()
                            ->length(4)
                            ->reactive()
                            ->disableAutocomplete()
                            ->mask(fn (TextInput\Mask $mask) => $mask->pattern('0000')),
                        TextInput::make('names')
                            ->required()
                            ->columnSpan(2),
                        Select::make("sex")
                            ->options([
                                "Male" => "Male",
                                "Female" => "Female"
                            ])
                            ->required()
                            ->searchable()
                            ->columnSpan(2),
                        TextInput::make('year_of_birth')
                            ->required()
                            ->mask(fn (TextInput\Mask $mask) => $mask->pattern('0000'))
                            ->length(4)
                            ->columnSpan(2),
                        PhoneInput::make('phone_number')
                            ->initialCountry('rw')
                            ->preferredCountries(['rw'])
                            ->separateDialCode(true)
                            ->columnSpan(4),
                        Block::make('location')
                            ->schema([
                                Select::make("location.province_id")
                                    ->label('Province')
                                    ->placeholder('Choose...')
                                    ->options(Province::all()->pluck('provincename', 'provincecode'))
                                    ->searchable()
                                    ->reactive()
                                    ->required(),
                                Select::make("location.district_id")
                                    ->label('Dictrict')
                                    ->placeholder('Choose...')
                                    ->options(function (Closure $get) {
                                        if ($get('location.province_id') != null) {
                                            return Province::find($get('location.province_id'))?->districts()->pluck('DistrictName', 'DistrictCode');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make("location.sector_id")
                                    ->label('Sector')
                                    ->placeholder('Choose...')
                                    ->options(function (Closure $get) {
                                        if ($get('location.district_id') != null) {
                                            // dd($get('district_id'));
                                            return District::find($get('location.district_id'))?->sectors()->pluck('SectorName', 'SectorCode');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make("location.cell_id")
                                    ->label('Cell')
                                    ->placeholder('Choose...')
                                    ->options(function (Closure $get) {
                                        if ($get('location.sector_id') != null) {
                                            return Sector::find($get('location.sector_id'))?->cells()->pluck('CellName', 'CellCode');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make("location.village_id")
                                    ->label('Village')
                                    ->placeholder('Choose...')
                                    ->options(function (Closure $get) {
                                        if ($get('location.cell_id') != null) {
                                            return Cell::find($get('location.cell_id'))?->villages()->pluck('VillageName', 'VillageCode');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),
                            ])
                            ->columns(5)
                            ->columnSpan(4),
                        DatePicker::make("registration_date")
                            ->default(date('Y-m-d'))
                            ->hidden(),
                        Repeater::make('insurances')
                            ->relationship('linkedInsurances')
                            ->schema([
                                Select::make("insurance_id")
                                    ->label('Insurance')
                                    ->options(Insurance::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->columnSpan(function (Closure $get) {
                                        if ($get('insurance_id') == null || $get('insurance_id') == 3) {
                                            return 3;
                                        } else if ($get('insurance_id') == 4 || $get('insurance_id') == 7 || $get('insurance_id') == 10 || $get('insurance_id') == 11 || $get('insurance_id') == 13 || $get('insurance_id') == 14 || $get('insurance_id') == 15) {
                                            return 2;
                                        }
                                    }),
                                TextInput::make('specific_data.member_number')
                                    ->required()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 4 && $get('insurance_id') != 10 && $get('insurance_id') != 11;
                                    }),
                                Select::make("specific_data.beneficiary")
                                    ->options([
                                        "Adherent lui-meme" => "Adherent lui-meme",
                                        "Conjoint" => "Conjoint",
                                        "Enfant" => "Enfant"
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 4;
                                    })
                                    ->reactive(),
                                TextInput::make('specific_data.affiliate_name')
                                    ->required()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 4 && $get('insurance_id') != 8;
                                    })
                                    ->columnSpan(function (Closure $get) {
                                        if ($get('insurance_id') == 4) {
                                            return 2;
                                        } else if ($get('insurance_id') == 8) {
                                            return 1;
                                        }
                                    }),
                                TextInput::make('specific_data.affiliate_affectation')
                                    ->required()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 4;
                                    }),
                                TextInput::make('specific_data.affiliation_number')
                                    ->required()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 5 && $get('insurance_id') != 6 && $get('insurance_id') != 9 && $get('insurance_id') != 12 && $get('insurance_id') != 13 && $get('insurance_id') != 14 && $get('insurance_id') != 15;
                                    }),
                                Select::make("specific_data.category_of_beneficiary")
                                    ->options([
                                        "Affiliated" => "Affiliated",
                                        "Dependent" => "Dependent",
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 5;
                                    })
                                    ->reactive(),
                                TextInput::make('specific_data.police_number')
                                    ->required()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 6 && $get('insurance_id') != 7 && $get('insurance_id') != 9 && $get('insurance_id') != 12;
                                    }),
                                Radio::make('is_affiliated')
                                    ->label('Is Affiliated?')
                                    ->boolean()
                                    ->hidden(function (Closure $get) {
                                        return $get('insurance_id') != 8;
                                    }),
                                Toggle::make('enabled')
                                    ->default(1)
                                    ->inline(false)
                                    ->required()
                            ])
                            ->columns(4)
                            ->columnSpan(4)
                            ->defaultItems(1),
                        Repeater::make('emergency_contacts')
                            ->relationship('emergencyContacts')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpan(4),
                                PhoneInpuT::make('phone_number')
                                    ->initialCountry('rw')
                                    ->preferredCountries(['rw'])
                                    ->separateDialCode(true)
                                    ->columnSpan(4),
                                Toggle::make('enabled')
                                    ->default(1)
                                    ->inline(false)
                                    ->required()
                            ])
                            ->columns(9)
                            ->columnSpan(4)
                            ->defaultItems(1),
                    ])
                    ->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->formatStateUsing(fn (File $record): string => sprintf("%05d", substr($record->number, 0)) . "/" . $record->registration_year)
                    ->searchable()
                    ->sortable()
                    ->hidden(),
                TextColumn::make('full_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('names')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('sex')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year_of_birth')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->sortable()
                    ->searchable(),
                TagsColumn::make("linkedInsurances.insurance_name")
                    ->label('Insurances')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereRelation('linkedInsurances', fn (Builder $query) => $query->whereRelation('insurance', 'name', 'like', "%{$search}%"));
                    }),
                TextColumn::make('emergencyContacts.name')
                    ->searchable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('emergencyContacts.phone_number')
                    ->searchable()
                    ->toggledHiddenByDefault()
            ])
            ->filters([
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
                        return isset($data['insurance_id']) ? $query->whereRelation('linkedInsurances', 'insurance_id', $data['insurance_id']) : $query;
                    })
            ])
            ->actions([
                Action::make('receive')
                    ->icon('heroicon-o-arrow-right')
                    ->action(function (array $data): void {
                        Session::create(array_merge($data, ['done_by' => auth()->user()->id]));
                    })
                    ->form(function (File $record) {
                        return [
                            Block::make('')
                                ->schema([
                                    DatePicker::make('date')
                                        ->required()
                                        ->maxDate(Carbon::now())
                                        ->default(Carbon::now()),
                                    Select::make('file_insurance_id')
                                        ->label('Insurance')
                                        ->options($record->linkedInsurances->pluck('insurance.name', 'id'))
                                        ->searchable()
                                        ->required()
                                        ->default($record->linkedInsurances()->first()->id)
                                        ->reactive(),
                                    Select::make('discount_id')
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
                                        ->default($record->linkedInsurances()->first()->insurance->discounts()->first()->id)
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
                                        })
                                ])
                                ->columns(2)
                        ];
                    }),
                Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    ViewAction::make(),
                    DeleteAction::make()
                ]),
                // ExportAction::make()
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                // FilamentExportBulkAction::make('export'),
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'view' => Pages\ViewFile::route('/{record}'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
