<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilelessInvoiceResource\Pages;
use App\Filament\Resources\FilelessInvoiceResource\RelationManagers;
use App\Filament\Resources\FilelessInvoiceResource\RelationManagers\PaymentsRelationManager;
use App\Models\Charge;
use App\Models\FilelessInvoice;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilelessInvoiceResource extends Resource
{
    protected static ?string $model = FilelessInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $modelLabel = 'quick bill';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 6;

    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('names')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date')
                            ->default(date('Y-m-d'))
                            ->required(),
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Hidden::make('done_by')
                                    ->default(auth()->user()->id),
                                Select::make('charge_id')
                                    ->label('Charge')
                                    ->options(Charge::where('charge_list_charge_type_id', 28)->pluck('name', 'id'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function (Closure $get, Closure $set, $state, $context, $record) {
                                        $charge = Charge::find($state);
                                        if ($charge) {
                                            $set('sold_at', $charge->price);
                                            $set('quantity', 1);
                                            $set('amount', $charge->price * 1);
                                        } else {
                                            $set('sold_at', '');
                                            $set('quantity', '');
                                            $set('amount', '');
                                        }
                                    })
                                    ->columnSpan(2)
                                    ->required(),
                                TextInput::make('sold_at')
                                    ->numeric()
                                    ->extraInputAttributes(["readonly" => "true"]),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function (Closure $get, Closure $set, $state) {
                                        $set('amount', $get('sold_at') * $state);
                                    })
                                    ->required(),
                                TextInput::make('amount')
                                    ->numeric()
                                    ->extraInputAttributes(["readonly" => "true"])

                            ])
                            ->columnSpan(2)
                            ->columns(5)
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('names')
                    ->label('Patient names')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TagsColumn::make('charges')
                    ->getStateUsing(function (FilelessInvoice $record) {
                        $charges = '';
                        foreach ($record->items as $key => $item) {
                            $charges .= $item->charge->name;
                            if ($key != ($record->items()->count('id') - 1)) {
                                $charges .= '::';
                            }
                            return $charges;
                        }
                    })
                    ->separator('::'),
                TextColumn::make('total_amount')
                    ->getStateUsing(fn (FilelessInvoice $record) => $record->items()->sum('amount'))
                    ->formatStateUsing(fn ($state) => "FRw " . $state),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->default(now()),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }

                        return 'Billed on ' . Carbon::parse($data['date'])->toFormattedDateString();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                        return $query->where('date', $data['date']);
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
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
            PaymentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFilelessInvoices::route('/'),
            'create' => Pages\CreateFilelessInvoice::route('/create'),
            'view' => Pages\ViewFilelessInvoice::route('/{record}'),
            'edit' => Pages\EditFilelessInvoice::route('/{record}/edit'),
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
