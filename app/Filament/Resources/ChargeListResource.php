<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChargeListResource\Pages;
use App\Filament\Resources\ChargeListResource\RelationManagers;
use App\Models\ChargeList;
use App\Models\ChargeType;
use App\Models\Insurance;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TagsColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ChargeListResource extends Resource
{
    protected static ?string $model = ChargeList::class;

    // protected static ?string $navigationIcon = 'iconpark-listone-o';

    protected static ?string $navigationGroup = 'Charges';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TagsColumn::make('linkedInsurances.insurance_name'),
                TagsColumn::make('linkedChargeTypes.charge_type_name')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChargeLists::route('/'),
            'create' => Pages\CreateChargeList::route('/create'),
            'edit' => Pages\EditChargeList::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getInsurancesFormField(): MultiSelect
    {
        return MultiSelect::make("insurances")
            ->relationship('insurances', 'name')
            ->options(Insurance::all()->pluck('name', 'id'))
            ->reactive();
    }

    public static function getChargeTypesFormField(): MultiSelect
    {
        return MultiSelect::make("chargeTypes")
            ->relationship('chargeTypes', 'name')
            ->options(ChargeType::all()->pluck('name', 'id'))
            ->reactive();
    }

    public static function getValidSinceFormField(): DatePicker
    {
        return DatePicker::make('valid_since')
            ->default(Carbon::now())
            ->required();
    }

    public static function getValidUntilFormField(): DatePicker
    {
        return DatePicker::make('valid_until');
    }

    public function hasSkippableSteps(): bool
    {
        return true;
    }
}
