<?php

namespace App\Filament\Resources\ChargeListResource\Pages;

use App\Filament\Resources\ChargeListResource;
use App\Models\ChargeType;
use App\Models\Insurance;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\HasWizard;

class EditChargeList extends EditRecord
{
    use HasWizard;
    
    protected static string $resource = ChargeListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getSteps(): array
    {
        return [
            Step::make("Basic")
                ->description('About the charge list')
                ->schema([
                    Card::make()
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            ChargeListResource::getValidSinceFormField(),
                            ChargeListResource::getValidUntilFormField(),
                            SpatieMediaLibraryFileUpload::make('source_file')
                                ->multiple()
                                ->enableReordering()
                                ->enableOpen()
                                ->enableDownload()
                                ->conversion('thumb')
                                ->columnSpan(2),
                        ])
                        ->columns(2)
                        ->columnSpan(2)
                ]),
            Step::make("Insurances")
                ->description('Linked insurances')
                ->schema([
                    Card::make()
                        ->schema([
                            Repeater::make('linkedInsurances')
                                ->relationship()
                                ->schema([
                                    Select::make("insurance_id")
                                        ->label('Insurance')
                                        ->options(Insurance::all()->pluck('name', 'id'))
                                        ->reactive()
                                        ->searchable()
                                        ->columnSpan(2)
                                        ->required(),
                                    ChargeListResource::getValidSinceFormField()
                                        ->columnSpan(2),
                                    ChargeListResource::getValidUntilFormField()
                                        ->columnSpan(2),
                                    Toggle::make('enabled')
                                        ->inline(false)
                                        ->default(true)
                                ])
                                ->collapsible()
                                ->defaultItems(1)
                                ->columns(7)
                        ])
                ]),
            Step::make("Charge Types")
                ->description('Reimbursable charge types')
                ->schema([
                    Card::make()
                        ->schema([
                            Repeater::make('linkedChargeTypes')
                                ->relationship()
                                ->schema([
                                    Select::make("charge_type_id")
                                        ->label('Charge type')
                                        ->options(ChargeType::all()->pluck('name', 'id'))
                                        ->reactive()
                                        ->searchable()
                                        ->columnSpan(2)
                                        ->required(),
                                    ChargeListResource::getValidSinceFormField()
                                        ->columnSpan(2),
                                    ChargeListResource::getValidUntilFormField()
                                        ->columnSpan(2),
                                    Toggle::make('enabled')
                                        ->inline(false)
                                        ->default(true)
                                ])
                                ->collapsible()
                                ->defaultItems(1)
                                ->columns(7)
                                ->cloneable()
                        ])
                ])
        ];
    }
}
