<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceInsuranceTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoicePatientTotalPrice;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceTotalPrice;
use App\Models\Invoice;
use App\Models\PaymentMean;
use App\Models\Session;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Actions\Concerns\HasAction;

class ViewInvoice extends ViewRecord
{
    use HasAction;
    protected static string $resource = InvoiceResource::class;
    protected static string $view = 'view-invoice';

    public ?Session $session = null;

    public function mount($record): void
    {
        $this->session = Invoice::find($record)->session;
        parent::mount($record);
    }

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return "Print";
                    }
                    // return "Preview";
                    return "Print";
                })
                ->color(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return "primary";
                    }
                    return "secondary";
                })
                // ->icon('fas-print')
                ->disabled(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return false;
                    }
                    return true;
                })
                ->action('printInvonce'),
            /* Action::make('payments')
                ->icon('fas-money-bill-1')
                ->color(function ($record) {
                    if ($this->record->payments()->count('id') > 0) {
                        return "secondary";
                    }
                    return "primary";
                })
                ->action(function (array $data): void {
                    // dd($data);
                })
                ->form([
                    Repeater::make('payments')
                        ->relationship()
                        ->schema([
                            Select::make('payment_mean_id')
                                ->label('Payment Mean')
                                ->relationship('paymentMean', 'name')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required(),
                                ])
                                ->options(PaymentMean::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->default(PaymentMean::all()->first() ? PaymentMean::all()->first()->id : null)
                                ->reactive(),
                            TextInput::make('amount')
                                ->numeric()
                                ->required()
                        ])
                        ->columns(2),
                ]), */
            ActionGroup::make([
                EditAction::make()
                    ->icon('heroicon-s-pencil')
                    ->color('secondary'),
                Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash'),
                Actions\ForceDeleteAction::make(),
                Actions\RestoreAction::make(),
            ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceTotalPrice::class,
            InvoiceInsuranceTotalPrice::class,
            InvoicePatientTotalPrice::class
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }

    protected function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    public function printInvonce()
    {
        $this->dispatchBrowserEvent('print-invoice');
    }
}
