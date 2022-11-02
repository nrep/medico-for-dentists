<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class InvoiceInsuranceTotalPrice extends Widget
{
    protected static string $view = 'filament.resources.invoice-resource.widgets.invoice-insurance-total-price';

    public ?Model $record = null;

    public $currency = "FRw";
}
