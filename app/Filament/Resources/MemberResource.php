<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\GlobalSearch\Actions\Action;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    // protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'nom';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'File' => $record->dossier,
            'Insurance' => $record->assureur,
            'Tel' => $record->phone
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nom', 'dossier', 'phone'];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('edit')
                ->button()
                ->icon('heroicon-s-pencil')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /* Forms\Components\TextInput::make('date')
                    ->required()
                    ->maxLength(249),
                Forms\Components\TextInput::make('year')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('nodemois')
                    ->required(),
                Forms\Components\TextInput::make('nodannee')
                    ->required(),
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(80), */
                Forms\Components\TextInput::make('sexe')
                    ->required()
                    ->maxLength(1),
                Forms\Components\TextInput::make('anneenaissance')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                /* Forms\Components\TextInput::make('ncac')
                    ->required()
                    ->maxLength(2), */
                Forms\Components\TextInput::make('dossier')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('assureur')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('province')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('district')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('sector')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('cell')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('village')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('affectation')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('bentype')
                    ->required()
                    ->maxLength(80),
                /* Forms\Components\TextInput::make('deleted')
                    ->required()
                    ->maxLength(20), */
                Forms\Components\TextInput::make('memberno')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('nom_personne')
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('policeno')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('tel_personne')
                    ->tel()
                    ->required()
                    ->maxLength(80),
                Forms\Components\TextInput::make('principal')
                    ->required()
                    ->maxLength(80),
                /* Forms\Components\TextInput::make('tm')
                    ->required(), */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /* Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('year'),
                Tables\Columns\TextColumn::make('nodemois'),
                Tables\Columns\TextColumn::make('nodannee'), */
                Tables\Columns\TextColumn::make('nom')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('sexe')
                    ->sortable(),
                Tables\Columns\TextColumn::make('anneenaissance')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dossier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assureur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('province')
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('district')
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('sector')
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('cell')
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('village')
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('affectation')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('bentype')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                // Tables\Columns\TextColumn::make('deleted'),
                Tables\Columns\TextColumn::make('memberno')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('nom_personne')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('policeno')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('tel_personne')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('principal')
                    ->searchable()
                    ->sortable()
                    ->toggledHiddenByDefault(),
                // Tables\Columns\TextColumn::make('tm'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
