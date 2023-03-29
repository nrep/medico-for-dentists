<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DebtResource\Pages;
use App\Filament\Resources\DebtResource\RelationManagers;
use App\Models\Debt;
use App\Models\File;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\MorphToSelect\Type;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInput;

class DebtResource extends Resource
{
    protected static ?string $model = Debt::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Forms\Components\MorphToSelect::make('debtable')
                        ->types([
                            Type::make(File::class)->titleColumnName('names')
                                ->label('Patient')
                                ->getOptionLabelFromRecordUsing(fn (File $record): string => "{$record->names} - " . sprintf('%05d', $record->number) . "/{$record->registration_year}"),
                            Type::make(Invoice::class)->titleColumnName('session_id')
                                ->label('Bill')
                                ->getOptionLabelFromRecordUsing(fn (Invoice $record): string => "Invoice Number PROV-" . sprintf("%06d", $record->session_id) . " for " . "{$record->session->fileInsurance->file->names} - " . sprintf('%05d', $record->session->fileInsurance->file->number) . "/{$record->session->fileInsurance->file->registration_year} on {$record->session->date}"),
                        ])
                        ->label('Responsible')
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('amount')
                        ->required(),
                    Forms\Components\DatePicker::make('payment_date')
                        ->required(),
                    Forms\Components\TextInput::make('payer_name')
                        ->required(),
                    PhoneInput::make('payer_phone_number')
                        ->initialCountry('rw')
                        ->preferredCountries(['rw'])
                        ->separateDialCode(true)
                        ->required(),
                    Forms\Components\Textarea::make('comment')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payer_name'),
                Tables\Columns\TextColumn::make('payer_phone_number'),
                Tables\Columns\TextColumn::make('bill_number'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\IconColumn::make('payment_status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('comment')
                    ->toggleable()
                    ->toggledHiddenByDefault(1),
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
            'index' => Pages\ListDebts::route('/'),
            'create' => Pages\CreateDebt::route('/create'),
            'view' => Pages\ViewDebt::route('/{record}'),
            'edit' => Pages\EditDebt::route('/{record}/edit'),
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
