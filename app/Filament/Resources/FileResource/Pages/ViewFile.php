<?php

namespace App\Filament\Resources\FileResource\Pages;

use App\Filament\Pages\InvoicesReport;
use App\Filament\Resources\FileResource;
use App\Models\File;
use App\Models\FileInsurance;
use App\Models\Session;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFile extends ViewRecord
{
    protected static string $resource = FileResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('receive')
                ->icon('heroicon-o-arrow-right')
                ->action(function (array $data): void {
                    $fileInsurance = FileInsurance::find($data['file_insurance_id']);
                    if ($fileInsurance?->insurance->id == 7 && isset($data['specific_data'])) {
                        $fileInsurance->specific_data = $$data['specific_data'];
                        $fileInsurance->save();
                    }
                    Session::create(array_merge($data, ['done_by' => auth()->user()->id]));
                })
                ->form(function () {
                    return [
                        Block::make('')
                            ->schema([
                                DatePicker::make('date')
                                    ->required()
                                    ->maxDate(Carbon::now())
                                    ->default(Carbon::now())
                                    ->columnSpan(2),
                                Select::make('file_insurance_id')
                                    ->label('Insurance')
                                    ->options($this->record->linkedInsurances->pluck('insurance.name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->default($this->record->linkedInsurances()->first()?->id)
                                    ->reactive()
                                    ->columnSpan(2),
                                TextInput::make('specific_data.first_name')
                                    ->required(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id == 7) {
                                                return true;
                                            }
                                        }
                                    })
                                    ->hidden(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id != 7) {
                                                return true;
                                            }
                                        }
                                    }),
                                TextInput::make('specific_data.last_name')
                                    ->required(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id == 7) {
                                                return true;
                                            }
                                        }
                                    })
                                    ->hidden(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id != 7) {
                                                return true;
                                            }
                                        }
                                    }),
                                TextInput::make('specific_data.scheme_name')
                                    ->required(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id == 7) {
                                                return true;
                                            }
                                        }
                                    })
                                    ->hidden(function (Closure $get) {
                                        if ($get('file_insurance_id')) {
                                            $insurance = FileInsurance::find($get('file_insurance_id'))->insurance;
                                            if ($insurance->id != 7) {
                                                return true;
                                            }
                                        }
                                    })
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                    ];
                }),
            EditAction::make()
                ->icon('heroicon-s-pencil'),
            ActionGroup::make([
                DeleteAction::make(),
            ]),
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }
}
