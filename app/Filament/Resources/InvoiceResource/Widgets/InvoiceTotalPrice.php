<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class InvoiceTotalPrice extends Widget
{
    protected static string $view = 'filament.resources.invoice-resource.widgets.invoice-total-price';

    public ?Model $record = null;

    public $currency = "FRw";
}
