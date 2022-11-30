<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceDayResource\Pages;
use App\Filament\Resources\InvoiceDayResource\RelationManagers;
use App\Models\InvoiceDay;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceDayResource extends Resource
{
    protected static ?string $model = InvoiceDay::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema(InvoiceResource::getDaysSchema())
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListInvoiceDays::route('/'),
            'create' => Pages\CreateInvoiceDay::route('/create'),
            'view' => Pages\ViewInvoiceDay::route('/{record}'),
            'edit' => Pages\EditInvoiceDay::route('/{record}/edit'),
        ];
    }
}
