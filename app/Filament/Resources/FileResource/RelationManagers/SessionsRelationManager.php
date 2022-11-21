<?php

namespace App\Filament\Resources\FileResource\RelationManagers;

use App\Filament\Resources\InvoiceResource;
use App\Models\Session;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $recordTitleAttribute = 'file_insurance_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('file_insurance_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice number')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => "PROV-" . sprintf("%06d", $state)),
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->getStateUsing(function (Session $record) {
                        $totalAmount = 0;

                        if ($record?->invoice?->charges()->count() > 0) {
                            foreach ($record?->invoice?->charges as $charge) {
                                $totalAmount += $charge->totalPrice;
                            }
                        }

                        return number_format($totalAmount);
                    }),
                TextColumn::make('insurance_pays')
                    ->label('Insurance')
                    ->getStateUsing(function (Session $record) {
                        $totalAmount = $insuracePays = 0;

                        if ($record?->invoice?->charges()->count() > 0) {
                            foreach ($record->invoice->charges as $key => $charge) {
                                $totalAmount += $charge->totalPrice;
                            }
                        }

                        if ($totalAmount > 0) {
                            $insuracePays = $totalAmount * $record->invoice->session->discount->discount / 100;
                        }

                        return number_format(round($insuracePays));
                    })
                    ->searchable(),
                TextColumn::make('patient_pays')
                    ->label('Patient')
                    ->getStateUsing(function (Session $record) {
                        $totalAmount = $patientPays = 0;

                        if ($record?->invoice?->charges()->count() > 0) {
                            foreach ($record->invoice->charges as $key => $charge) {
                                $totalAmount += $charge->totalPrice;
                            }
                        }

                        if ($totalAmount > 0) {
                            $patientPays = $totalAmount * (100 - $record->invoice->session->discount->discount) / 100;
                        }

                        return number_format(round($patientPays));
                    })
                    ->searchable(),
                TextColumn::make('paid')
                    ->label('Paid')
                    ->getStateUsing(fn (Session $record) => $record?->invoice?->payments()->sum('amount'))
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->searchable(),
                TextColumn::make('paid_vs_patient_pays')
                    ->label('Difference')
                    ->getStateUsing(function (Session $record) {
                        $totalAmount = $patientPays = 0;

                        if ($record?->invoice?->charges()->count() > 0) {
                            foreach ($record?->invoice?->charges as $key => $charge) {
                                $totalAmount += $charge->totalPrice;
                            }
                        }

                        if ($totalAmount > 0) {
                            $patientPays = $totalAmount * (100 - $record->invoice->session->discount->discount) / 100;
                        }

                        return number_format(round($record?->invoice?->payments()->sum('amount') - $patientPays));
                    })
                    ->searchable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('invoice.session.recordedBy.name')
                    ->toggledHiddenByDefault(),
                TagsColumn::make('consulted_by')
                    ->separator(',')
                    ->toggledHiddenByDefault(),
                TagsColumn::make('paid_to')
                    ->separator(',')
                    ->toggledHiddenByDefault(),
                TagsColumn::make('collaborators')
                    ->separator(',')
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Session $record) => $record->invoice?->id ? InvoiceResource::getUrl('view', ['record' => $record->invoice?->id]) : null)
                    ->hidden(fn (Session $record) => !$record->invoice?->id)
                    ->color('primary')
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
