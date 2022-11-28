<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\InvoiceDay;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DaysRelationManager extends RelationManager
{
    protected static string $relationship = 'days';

    protected static ?string $recordTitleAttribute = 'invoice_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->getStateUsing(function (InvoiceDay $record) {
                        foreach ($record->invoice->days as $key => $day) {
                            if ($day->id == $record->id) {
                                return $key + 1;
                            }
                        }
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->searchable()
                    ->sortable()
                    ->date(),
                TextColumn::make('total')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (InvoiceDay $record) {
                        return $record->items()->sum('total_price');
                    }),
                TextColumn::make('insurance')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (InvoiceDay $record) {
                        $total = $record->items()->sum('total_price');
                        $discount = $record->invoice->session->discount->discount;
                        return $discount > 0 ? round($total * ($discount / 100)) : 0;
                    }),
                TextColumn::make('patient')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (InvoiceDay $record) {
                        $total = $record->items()->sum('total_price');
                        $insuredPays = $record->invoice->session->discount->insured_pays;
                        return $insuredPays > 0 ? round($total * ($insuredPays / 100)) : 0;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                /* ViewAction::make()
                    ->url(fn (InvoiceDay $record) => "/invoice-days/{$record->id}"), */
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
