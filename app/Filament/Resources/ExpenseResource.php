<?php

namespace App\Filament\Resources;

use App\Exports\ExpensesExport;
use App\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use App\Filament\Resources\ExpenseResource\Pages\EditExpense;
use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use App\Filament\Resources\ExpenseResource\Pages\ViewExpense;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\BudgetAccount;
use App\Models\BudgetLine;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\File;
use App\Models\PaymentMean;
use App\Models\ServiceProvider;
use App\Models\Supplier;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Forms\Components\Select;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    protected function getTableFiltersFormColumns(): int
    {
        return 2;
    }

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
                            ->maxLength(255)
                            ->unique(),
                        Forms\Components\MorphToSelect::make('expenseable')
                            ->types([
                                Type::make(Employee::class)->titleColumnName('names'),
                                Type::make(Supplier::class)->titleColumnName('name'),
                                Type::make(ServiceProvider::class)->titleColumnName('name'),
                                Type::make(File::class)->titleColumnName('names')
                                    ->label('Patient')
                                    ->getOptionLabelFromRecordUsing(fn (File $record): string => "{$record->names} - " . sprintf('%05d', $record->number) . "/{$record->registration_year}"),
                            ])
                            ->label('Receiver')
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
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->datalist(fn () => ExpenseItem::all()->pluck('reason', 'reason')),
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
                Tables\Columns\TextColumn::make('bill_no')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMean.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenseable_id')
                    ->label('Receiver')
                    ->getStateUsing(function (Expense $record) {
                        return $record->expenseable?->names ?? $record->expenseable?->name;
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->getStateUsing(fn (Expense $record) => $record->items()->sum('amount')),
                Tables\Columns\TagsColumn::make('items.line.name')
                    ->label('Budget Line')
                    ->getStateUsing(function (Expense $record) {
                        $budgetLines = $record->items()->pluck('budget_line_id')->toArray();
                        $budgetLines1 = BudgetLine::whereIn('id', $budgetLines)->pluck('name')->toArray();
                        // Join using a comma
                        return implode(', ', $budgetLines1);
                    })
                    ->separator(','),
                Tables\Columns\TagsColumn::make('items.reason')
                    ->label('Reasons'),
                Tables\Columns\TagsColumn::make('items.ebm_bill_number')
                    ->label('EBM Bill Number')
            ])
            ->filters([
                Filter::make('budget-line')
                    ->form([
                        Select::make('id')
                            ->label("Budget Line")
                            ->options(BudgetLine::all()->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['id']) {
                            return null;
                        }

                        return 'Budget Line: ' . BudgetLine::find($data['id'])?->name;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['id']) ? $query->whereRelation('items', 'budget_line_id', $data['id']) : $query;
                    }),
                Filter::make('reason')
                    ->form([
                        Select::make('reason')
                            ->label("Reason")
                            ->options(ExpenseItem::all()->pluck('reason', 'reason'))
                            ->searchable(),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['reason']) {
                            return null;
                        }

                        return 'Reason: ' . $data['reason'];
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['reason']) ? $query->whereRelation('items', 'reason', $data['reason']) : $query;
                    }),
                Filter::make('since')
                    ->form([
                        DatePicker::make('since')
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['since']) {
                            return null;
                        }

                        return 'Since ' . Carbon::parse($data['since'])->toFormattedDateString();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $data['date'] = Carbon::parse($data['since'])->format('Y-m-d');
                        return isset($data['since']) ? $query->where('date', '>=', $data['date']) : $query;
                    }),
                Filter::make('until')
                    ->form([
                        DatePicker::make('until')
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['until']) {
                            return null;
                        }

                        return 'Until ' . Carbon::parse($data['until'])->toFormattedDateString();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $data['date'] = Carbon::parse($data['until'])->format('Y-m-d');
                        return isset($data['date']) ? $query->where('date', '<=', $data['date']) : $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->action(fn ($records) => Excel::download(new ExpensesExport($records), "Expenses Report.xlsx")),
                // ExportBulkAction::make(),
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
