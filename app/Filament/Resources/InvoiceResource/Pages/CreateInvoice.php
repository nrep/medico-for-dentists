<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\Session;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public ?Session $session = null;

    public function mount(): void
    {
        $this->session = Session::find(request('session'));
        parent::mount();
    }
}
