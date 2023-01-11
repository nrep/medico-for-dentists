<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\BudgetAccount;
use App\Models\BudgetLine;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\PaymentMean;
use App\Models\ServiceProvider;
use App\Models\Supplier;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Accountancy\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use Modules\Accountancy\Filament\Resources\ExpenseResource\Pages\EditExpense;
use Modules\Accountancy\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use Modules\Accountancy\Filament\Resources\ExpenseResource\Pages\ViewExpense;
use Savannabits\FilamentModules\Concerns\ContextualResource;

class ExpenseResource extends Resource
{

    use ContextualResource;

    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\Select::make('year')
                            ->options([
                                '2023' => '2023',
                                '2024' => '2024'
                            ])
                            ->searchable()
                            ->reactive()
                            ->required(),
                        Forms\Components\Select::make('budget_account_id')
                            ->label('Budget Account')
                            ->options(BudgetAccount::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('payment_mean_id')
                            ->label('Payment Mean')
                            ->relationship('paymentMean', 'name')
                            ->options(PaymentMean::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(PaymentMean::all()->first() ? PaymentMean::all()->first()->id : null)
                            ->reactive(),
                        Forms\Components\TextInput::make('bill_no')
                            ->label('Bill Number')
                            ->maxLength(255),
                        Forms\Components\MorphToSelect::make('expenseable')
                            ->types([
                                Type::make(Employee::class)->titleColumnName('names'),
                                Type::make(Supplier::class)->titleColumnName('name'),
                                Type::make(ServiceProvider::class)->titleColumnName('name'),
                            ])
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('date')
                            ->default(Carbon::now())
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('reason')
                                    ->maxLength(255)
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\Select::make('budget_line_id')
                                    ->label('Budget Line')
                                    ->options(function ($livewire) {
                                        if ($livewire->data != null && is_array($livewire->data) && isset($livewire->data['year'])) {
                                            return BudgetLine::where('year', $livewire->data['year'])->pluck('name', 'id');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('amount')
                                    ->maxLength(255)
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\Toggle::make('is_ebm_billed')
                                    ->inline(false)
                                    ->default(true)
                                    ->reactive()
                                    ->required(),
                                Forms\Components\TextInput::make('ebm_bill_number')
                                    ->maxLength(255)
                                    ->required(function (Closure $get) {
                                        if ($get('is_ebm_billed') != null && $get('is_ebm_billed') == true) {
                                            return $get('is_ebm_billed');
                                        }
                                        return false;
                                    })
                                    ->hidden(function (Closure $get) {
                                        if ($get('is_ebm_billed') != null && $get('is_ebm_billed') == true) {
                                            return !$get('is_ebm_billed');
                                        }
                                        return true;
                                    }),
                                Forms\Components\Textarea::make('comment')
                                    ->maxLength(65535)
                                    ->rows(2)
                                    ->cols(20)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->columns(4),
                        Forms\Components\Textarea::make('comment')
                            ->maxLength(65535)
                            ->rows(2)
                            ->cols(20)
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bill_no'),
                Tables\Columns\TextColumn::make('account.name'),
                Tables\Columns\TextColumn::make('paymentMean.name'),
                Tables\Columns\TextColumn::make('expenseable_id')
                    ->label('Receiver')
                    ->getStateUsing(function (Expense $record) {
                        return $record->expenseable?->names ?? $record->expenseable?->name;
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
                Tables\Columns\TextColumn::make('amount')
                    ->getStateUsing(fn (Expense $record) => $record->items()->sum('amount')),
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
            'index' => ListExpenses::route('/'),
            'create' => CreateExpense::route('/create'),
            'view' => ViewExpense::route('/{record}'),
            'edit' => EditExpense::route('/{record}/edit'),
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
