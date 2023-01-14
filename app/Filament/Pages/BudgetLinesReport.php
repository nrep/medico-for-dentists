<?php

namespace App\Filament\Pages;

use App\Models\BudgetLine;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class BudgetLinesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.budget-lines-report';

    protected static ?string $navigationLabel = 'Budget Lines';

    protected static ?string $navigationGroup = 'Reports';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder|Relation
    {
        return BudgetLine::query();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('year')
                ->searchable()
                ->sortable(),
            TextColumn::make('initial_amount')
                ->money('rwf')
                ->searchable()
                ->sortable(),
            TextColumn::make('id')
                ->label('Used amount')
                ->getStateUsing(fn (BudgetLine $record) => $record->expenseItems()->sum('amount'))
                ->money('rwf'),
            TextColumn::make('idd')
                ->label('Remaining amount')
                ->getStateUsing(fn (BudgetLine $record) => $record->initial_amount - $record->expenseItems()->sum('amount'))
                ->money('rwf')
        ];
    }
}
