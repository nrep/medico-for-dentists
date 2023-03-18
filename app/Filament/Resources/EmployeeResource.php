<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\EmployeeCategory;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInput;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    // protected static ?string $navigationIcon = 'clarity-employee-group-line';

    protected static ?string $navigationGroup = 'HR';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make("names")
                            ->required(),
                        Select::make("sex")
                            ->options([
                                "Male" => "Male",
                                "Female" => "Female"
                            ])
                            ->searchable()
                        /* ->required() */,
                        PhoneInput::make('phone_number')
                            ->initialCountry('rw')
                            ->preferredCountries(['rw'])
                            ->separateDialCode(true),
                        Select::make("degree")
                            ->options([
                                "None" => "None",
                                "A2" => "A2",
                                "A1" => "A1",
                                "A0" => "A0",
                                'Masters' => 'Masters',
                                "PhD" => "PhD"
                            ])
                            ->required()
                            ->default("None")
                            ->searchable(),
                        DatePicker::make("started_at")
                            ->columnSpan(2),
                        Repeater::make('categories')
                            ->relationship()
                            ->schema([
                                Select::make('employee_category_id')
                                    ->label('Category')
                                    ->options(EmployeeCategory::all()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->searchable(),
                            ])
                            ->columnSpan(2),
                        /* Section::make('Specific data')
                            // ->statePath('specific_data')
                            ->schema(function (Closure $get) {
                                $inputs  = [];
                                foreach ($get('employee_categories') as $employeeCategory) {
                                    $specificInputs = EmployeeCategory::find($employeeCategory)->specificInputs;
                                    foreach ($specificInputs as $specificInput) {
                                        if ($specificInput->type == 'input') {
                                            $inputs[] = TextInput::make("specific_data." . $specificInput->name)
                                                ->columnSpan(function () use ($specificInputs) {
                                                    if (count($specificInputs) > 1) {
                                                        return 1;
                                                    }
                                                    return 2;
                                                });
                                        } else if ($specificInput->type == 'select') {
                                            // $options = [];
                                            $inputs[] = Select::make("specific_data." . $specificInput->name)
                                                ->searchable()
                                                ->options(function () use ($specificInput) {
                                                    $options = [];
                                                    $explodedOptions = explode(",", $specificInput->options);

                                                    foreach ($explodedOptions as $explodedOption) {
                                                        $options[$explodedOption] = $explodedOption;
                                                    }

                                                    return $options;
                                                });
                                        }
                                    }
                                }
                                return $inputs;
                            })
                            ->collapsible()
                            ->hidden(function (Closure $get) {
                                $showSection = false;
                                foreach ($get('employee_categories') as $employeeCategory) {
                                    if (EmployeeCategory::find($employeeCategory)->specificInputs) {
                                        $showSection = true;
                                    }
                                }
                                return !$showSection;
                            })
                            ->columns(2) */
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("names")
                    ->searchable(),
                TextColumn::make("sex")
                    ->searchable()
                    ->hidden(),
                TextColumn::make("phone_number")
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
