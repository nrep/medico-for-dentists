<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChargeResource\Pages;
use App\Filament\Resources\ChargeResource\RelationManagers;
use App\Models\Charge;
use App\Models\ChargeList;
use App\Models\Insurance;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput\Mask;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChargeResource extends Resource
{
    protected static ?string $model = Charge::class;

    // protected static ?string $navigationIcon = 'eos-product-subscriptions-o';

    protected static ?string $navigationGroup = 'Charges';

    public $chargeList;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\Select::make('charge_list')
                            ->options(ChargeList::all()->pluck('title', 'id'))
                            ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                                if ($state) {
                                    $chargeList = ChargeList::find($state);
                                    if ($get('valid_since') == null) {
                                        $set('valid_since', $chargeList->valid_since);
                                    }

                                    if ($get('valid_until') == null) {
                                        $set('valid_until', $chargeList->valid_since);
                                    }
                                }
                            })
                            ->required()
                            ->searchable()
                            ->reactive(),
                        Forms\Components\Select::make('charge_list_charge_type_id')
                            ->label('Charge type')
                            ->options(function (callable $get) {
                                if ($get('charge_list')) {
                                    return ChargeList::find($get('charge_list'))->linkedChargeTypes->pluck('chargeType.name', 'id');
                                }
                                return [];
                            })
                            ->required()
                            ->searchable()
                            ->reactive(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->mask(
                                fn (Mask $mask) => $mask
                                    ->patternBlocks([
                                        'money' => fn (Mask $mask) => $mask
                                            ->numeric()
                                            ->thousandsSeparator(',')
                                            ->decimalSeparator('.'),
                                    ])
                                    ->pattern('FRwmoney'),
                            )
                            ->required()
                            ->reactive(),
                        Forms\Components\DatePicker::make('valid_since')
                            ->reactive(),
                        Forms\Components\DatePicker::make('valid_until'),
                        Forms\Components\Toggle::make('enabled')
                            ->required()
                            ->default(1)
                            ->columnSpan(2),
                        Repeater::make('conditions')
                            ->relationship()
                            ->schema([
                                MultiSelect::make('insurances')
                                    ->options(function ($livewire) {
                                        if ($livewire->data["charge_list"]) {
                                            return ChargeList::find($livewire->data["charge_list"])->linkedInsurances->pluck('insurance.name', 'insurance.id');
                                        }
                                        return [];
                                    }),
                                Hidden::make("context")
                                    ->default("billing")
                                    ->reactive(),
                                Hidden::make('type')
                                    ->default('times-over-period')
                                    ->reactive(),
                                KeyValue::make('condition')
                                    ->default(function (Closure $get) {
                                        if ($get("type") == "times-over-period") {
                                            return [
                                                "times" => "",
                                                "period" => ""
                                            ];
                                        }
                                        return [];
                                    })
                                    ->disableAddingRows()
                                    ->disableEditingKeys()
                                    ->disableDeletingRows()
                            ])
                            ->columnSpan(2)
                            ->collapsed(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('rwf')
                    ->searchable()
                    ->sortable(),
                TagsColumn::make("insurances.name")
                    ->label("Insurance")
                    ->getStateUsing(fn (Charge $record) => $record->chargeListChargeType->chargeList->linkedInsurances()->first()?->insurance->name)
                    ->separator(',')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                DeleteAction::make(),
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
            'index' => Pages\ListCharges::route('/'),
            'create' => Pages\CreateCharge::route('/create'),
            'edit' => Pages\EditCharge::route('/{record}/edit'),
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
