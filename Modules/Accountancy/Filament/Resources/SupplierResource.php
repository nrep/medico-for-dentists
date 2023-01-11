<?php

namespace Modules\Accountancy\Filament\Resources;

use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Accountancy\Filament\Resources\SupplierResource\Pages\ManageSuppliers;
use Savannabits\FilamentModules\Concerns\ContextualResource;
use Ysfkaya\FilamentPhoneInput\PhoneInput;

class SupplierResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                PhoneInput::make('phone_number')
                    ->initialCountry('rw')
                    ->preferredCountries(['rw'])
                    ->separateDialCode(true),
                TextInput::make('tin')
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('phone_number'),
                TextColumn::make('tin'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSuppliers::route('/'),
        ];
    }
}
