<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetLineResource\Pages\CreateBudgetLine;
use App\Filament\Resources\BudgetLineResource\Pages\EditBudgetLine;
use App\Filament\Resources\BudgetLineResource\Pages\ListBudgetLines;
use App\Filament\Resources\BudgetLineResource\Pages\ViewBudgetLine;
use App\Filament\Resources\BudgetLineResource\RelationManagers;
use App\Models\BudgetLine;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BudgetLineResource extends Resource
{
    protected static ?string $model = BudgetLine::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('year')
                            ->required()
                            ->minLength(4)
                            ->maxLength(4)
                            ->numeric(),
                        Forms\Components\TextInput::make('initial_amount')
                            ->required()
                            ->columnSpan(2)
                            ->numeric(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('year')
                    ->date(),
                Tables\Columns\TextColumn::make('initial_amount'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
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
            'index' => ListBudgetLines::route('/'),
            'create' => CreateBudgetLine::route('/create'),
            'view' => ViewBudgetLine::route('/{record}'),
            'edit' => EditBudgetLine::route('/{record}/edit'),
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
