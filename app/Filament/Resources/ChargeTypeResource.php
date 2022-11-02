<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChargeTypeResource\Pages;
use App\Filament\Resources\ChargeTypeResource\RelationManagers;
use App\Models\ChargeType;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChargeTypeResource extends Resource
{
    protected static ?string $model = ChargeType::class;

    // protected static ?string $navigationIcon = 'iconpark-categorymanagement-o';

    protected static ?string $navigationGroup = 'Charges';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6)
                            ->reactive()
                            ->unique(ChargeType::class, 'name'),
                        Forms\Components\Toggle::make('enabled')
                            ->required()
                            ->inline(false)
                            ->default(1),
                    ])
                    ->columns(7)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('enabled')
                    ->searchable()
                    ->sortable(),
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
            'index' => Pages\ListChargeTypes::route('/'),
            'create' => Pages\CreateChargeType::route('/create'),
            'edit' => Pages\EditChargeType::route('/{record}/edit'),
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
