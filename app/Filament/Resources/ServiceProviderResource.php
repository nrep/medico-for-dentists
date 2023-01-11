<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProviderResource\Pages;
use App\Filament\Resources\ServiceProviderResource\RelationManagers;
use App\Models\ServiceProvider;
use Closure;
use Filament\Forms;
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
use Modules\Accountancy\Filament\Resources\ServiceProviderResource\Pages\ManageServiceProviders;
use Savannabits\FilamentModules\Concerns\ContextualResource;
use Ysfkaya\FilamentPhoneInput\PhoneInput;

class ServiceProviderResource extends Resource
{
    use ContextualResource;

    protected static ?string $model = ServiceProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationGroup = 'Accountancy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'company' => 'Company',
                        'personnel' => 'Personnel',
                    ])
                    ->required()
                    ->searchable()
                    ->reactive(),
                TextInput::make('tin')
                    ->required(fn (Closure $get) => $get('type') === 'company')
                    ->hidden(fn (Closure $get) => $get('type') !== 'company'),
                TextInput::make('idno')
                    ->required(fn (Closure $get) => $get('type') === 'personnel')
                    ->hidden(fn (Closure $get) => $get('type') !== 'personnel'),
                PhoneInput::make('phone_number')
                    ->initialCountry('rw')
                    ->preferredCountries(['rw'])
                    ->separateDialCode(true)
                    ->columnSpan(fn (Closure $get) => $get('type') !== null ? 1 : 2),
                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('identifier')
                    ->getStateUsing(fn (ServiceProvider $record) => $record->type === 'company' ? $record->tin : $record->idno)
                    ->formatStateUsing(fn (ServiceProvider $record, $state) => $record->type === 'company' ? 'TIN: ' . $state : 'IDNO: ' . $state),
                TextColumn::make('phone_number'),
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
            'index' => ManageServiceProviders::route('/'),
        ];
    }
}
