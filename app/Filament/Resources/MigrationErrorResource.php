<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MigrationErrorResource\Pages;
use App\Filament\Resources\MigrationErrorResource\RelationManagers;
use App\Models\MigrationError;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MigrationErrorResource extends Resource
{
    protected static ?string $model = MigrationError::class;

    // protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('from_table_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('from_table_id')
                    ->required(),
                Forms\Components\TextInput::make('to_table_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('to_table_id')
                    ->required(),
                Forms\Components\TextInput::make('data')
                    ->required(),
                Forms\Components\TextInput::make('error_message'),
                Forms\Components\TextInput::make('error_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('resolved')
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_table_type')
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('from_table_id')
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('to_table_type')
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('to_table_id')
                    ->sortable()
                    ->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('model_title')
                    ->searchable()
                    ->sortable(),
                /* Tables\Columns\TextColumn::make('data'),
                Tables\Columns\TextColumn::make('error_message')
                    ->searchable()
                    ->sortable(), */
                Tables\Columns\TextColumn::make('error_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('resolved')
                    ->sortable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable()
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListMigrationErrors::route('/'),
            'create' => Pages\CreateMigrationError::route('/create'),
            'edit' => Pages\EditMigrationError::route('/{record}/edit'),
        ];
    }
}
