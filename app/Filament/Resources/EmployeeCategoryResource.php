<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeCategoryResource\Pages;
use App\Filament\Resources\EmployeeCategoryResource\RelationManagers;
use App\Models\EmployeeCategory;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
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

class EmployeeCategoryResource extends Resource
{
    protected static ?string $model = EmployeeCategory::class;

    // protected static ?string $navigationIcon = 'codicon-type-hierarchy';

    protected static ?string $navigationGroup = 'HR';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->unique(EmployeeCategory::class, 'name'),
                        Select::make('type')
                            ->options([
                                "Health professional" => "Health professional",
                                "Supporting staff" => "Supporting staff"
                            ])
                            ->required()
                            ->searchable()
                            ->reactive(),
                        /* Repeater::make('specific_columns')
                            ->relationship('specificInputs')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpan(function (Closure $get) {
                                        if ($get('type') !== "select") {
                                            return 2;
                                        }
                                        return 1;
                                    }),
                                Select::make('type')
                                    ->options([
                                        "input" => "Text Input",
                                        "select" => "Select"
                                    ])
                                    ->searchable()
                                    ->reactive(),
                                TextInput::make('default_value'),
                                TagsInput::make('options')
                                    ->separator(',')
                                    ->required(fn (Closure $get) => $get('type') === "select")
                                    ->hidden(fn (Closure $get) => $get('type') !== "select"),
                            ])
                            ->columns(4)
                            ->columnSpan(2)
                            ->collapsed() */
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make()
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
            'index' => Pages\ListEmployeeCategories::route('/'),
            'create' => Pages\CreateEmployeeCategory::route('/create'),
            'edit' => Pages\EditEmployeeCategory::route('/{record}/edit'),
        ];
    }
}
