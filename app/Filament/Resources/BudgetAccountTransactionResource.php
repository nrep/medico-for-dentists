<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetAccountTransactionResource\RelationManagers;
use App\Filament\Resources\BudgetAccountTransactionResource\Pages\CreateBudgetAccountTransaction;
use App\Filament\Resources\BudgetAccountTransactionResource\Pages\EditBudgetAccountTransaction;
use App\Filament\Resources\BudgetAccountTransactionResource\Pages\ListBudgetAccountTransactions;
use App\Filament\Resources\BudgetAccountTransactionResource\Pages\ViewBudgetAccountTransaction;
use App\Models\BudgetAccount;
use App\Models\BudgetAccountTransaction;
use App\Models\BudgetSource;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BudgetAccountTransactionResource extends Resource
{
    protected static ?string $model = BudgetAccountTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    protected static ?string $modelLabel = 'account transaction';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Select::make('budget_source_id')
                            ->label('Budget Source')
                            ->options(BudgetSource::all()->pluck('name', 'id'))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                            ])
                            ->createOptionUsing(function ($data) {
                                return BudgetSource::create($data);
                            })
                            ->searchable()
                            ->required(),
                        Select::make('budget_account_id')
                            ->label('Budget Account')
                            ->options(BudgetAccount::all()->pluck('name', 'id'))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                            ])
                            ->createOptionUsing(function ($data) {
                                return BudgetAccount::create($data);
                            })
                            ->searchable()
                            ->required(),
                        Hidden::make('nature')
                            ->default('credit')
                            ->required(),
                        TextInput::make('amount')
                            ->numeric()
                            ->mask(
                                fn (TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->decimalPlaces(2) // Set the number of digits after the decimal point.
                                    ->decimalSeparator(',') // Add a separator for decimal numbers.
                                    ->integer() // Disallow decimal numbers.
                                    ->mapToDecimalSeparator([',']) // Map additional characters to the decimal separator.
                                    ->minValue(1) // Set the minimum value that the number can be.
                                    ->normalizeZeros() // Append or remove zeros at the end of the number.
                                    ->padFractionalZeros() // Pad zeros at the end of the number to always maintain the maximum number of decimal places.
                                    ->thousandsSeparator(','), // Add a separator for thousands.
                            )
                            ->required(),
                        DatePicker::make('date')
                            ->default(Carbon::now()->format('Y-m-d'))
                            ->required(),
                        Textarea::make('description')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source.name')
                    ->label('Budget Source')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('account.name')
                    ->label('Budget Account')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nature')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ',')),
                TextColumn::make('date')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->toFormattedDateString()),
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
            'index' => ListBudgetAccountTransactions::route('/'),
            'create' => CreateBudgetAccountTransaction::route('/create'),
            'view' => ViewBudgetAccountTransaction::route('/{record}'),
            'edit' => EditBudgetAccountTransaction::route('/{record}/edit'),
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
