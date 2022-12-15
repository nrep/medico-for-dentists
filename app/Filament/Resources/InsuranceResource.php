<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InsuranceResource\Pages;
use App\Filament\Resources\InsuranceResource\RelationManagers;
use App\Models\Insurance;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Filters\MultiSelectFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InsuranceResource extends Resource
{
    protected static ?string $model = Insurance::class;

    // protected static ?string $navigationIcon = 'ri-building-2-line';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->unique(Insurance::class, 'name')
                            ->columnSpan(2)
                            ->autofocus(),
                        Repeater::make('discounts')
                            ->relationship('discounts')
                            ->schema([
                                TextInput::make('display_name')
                                    ->required()
                                    ->reactive(),
                                TextInput::make('discount')
                                    ->numeric()
                                    ->required()
                                    ->reactive(),
                                TextInput::make('insured_pays')
                                    ->numeric()
                                    ->required(),
                                Toggle::make('enabled')
                                    ->default(1)
                                    ->inline(false)
                            ])
                            ->columns(4)
                            ->columnSpan(2)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                dd($state);
                            })
                            ->afterStateHydrated(function ($state) {
                                $repeaterData = $state[array_keys($state)[0]];
                                if ($repeaterData['discount']) {
                                    dd($repeaterData);
                                }
                            }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TagsColumn::make("discounts.display_name")
                    ->label('Patient Pays')
                    ->searchable()
            ])
            ->filters([
                // SelectFilter::make('discounts')->relationship('discounts', 'display_name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ReplicateAction::make()
                    ->excludeAttributes(['name'])
                    ->form([
                        TextInput::make('name')->required(),
                    ])
                    ->beforeReplicaSaved(function (Model $replica, array $data): void {
                        $replica->fill($data);
                    }),
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
            'index' => Pages\ListInsurances::route('/'),
            'create' => Pages\CreateInsurance::route('/create'),
            'edit' => Pages\EditInsurance::route('/{record}/edit'),
        ];
    }
}
