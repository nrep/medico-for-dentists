<?php

namespace App\Filament\Pages;

use App\Exports\InvoiceReportExport;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentMean;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserInvoicesReport extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'tabler-report-analytics';

    protected static string $view = 'filament.pages.user-invoices-report';

    protected static ?string $navigationLabel = 'Bills';

    protected static ?string $navigationGroup = 'Reports';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder
    {
        $query = InvoicePayment::select(
            'invoice_payments.done_by',
            'users.name',
            DB::raw("COUNT(*) AS total"),
            DB::raw('SUM(amount) AS paid'),
            DB::raw('GROUP_CONCAT(DISTINCT(invoice_payments.invoice_id)) AS invoice_ids')
        )
            ->join('users', 'invoice_payments.done_by', 'users.id')
            ->groupBy(['invoice_payments.done_by', 'users.name']);
        return $query;
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('name')
                ->label('User name')
                ->searchable()
                ->sortable(),
            TextColumn::make('total')
                ->label('Patients')
                ->searchable()
                ->sortable(),
            TextColumn::make('total_amount')
                ->getStateUsing(function (InvoicePayment $record) {
                    $insuranceDiscounts = 0;

                    foreach (explode(",", $record->invoice_ids) as $invoiceId) {
                        $invoice = Invoice::find($invoiceId);
                        $insuranceDiscounts += $invoice->charges()->sum('total_price');
                    }

                    return $insuranceDiscounts;
                })
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->searchable()
                ->sortable(),
            TextColumn::make('Insurance discounts')
                ->getStateUsing(function (InvoicePayment $record) {
                    $insuranceDiscounts = 0;

                    foreach (explode(",", $record->invoice_ids) as $invoiceId) {
                        $invoice = Invoice::find($invoiceId);
                        $insuranceDiscounts += ($invoice->charges()->sum('total_price') * $invoice->session->discount->discount) / 100;
                    }

                    return $insuranceDiscounts;
                })
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->searchable()
                ->sortable(),
            TextColumn::make('Patient to pay')
                ->getStateUsing(function (InvoicePayment $record) {
                    $insuranceDiscounts = 0;

                    foreach (explode(",", $record->invoice_ids) as $invoiceId) {
                        $invoice = Invoice::find($invoiceId);
                        $insuranceDiscounts += ($invoice->charges()->sum('total_price') * $invoice->session->discount->insured_pays) / 100;
                    }

                    return $insuranceDiscounts;
                })
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->searchable()
                ->sortable(),
            TextColumn::make('paid')
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->searchable()
                ->sortable(),
        ];

        $paymentMeans = PaymentMean::all();

        foreach ($paymentMeans as $paymentMean) {
            $columns[] = TextColumn::make($paymentMean->name)
                ->getStateUsing(function (InvoicePayment $record) use ($paymentMean) {
                    $insuranceDiscounts = 0;

                    foreach (explode(",", $record->invoice_ids) as $invoiceId) {
                        $invoice = Invoice::find($invoiceId);
                        $insuranceDiscounts += $invoice->payments()->where('payment_mean_id', $paymentMean->id)->where('done_by', $record->done_by)->sum("amount");
                    }

                    return $insuranceDiscounts;
                })
                ->formatStateUsing(fn ($state) => "RWF " . number_format($state))
                ->toggledHiddenByDefault();
        }

        return $columns;
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->done_by;
    }

    protected function getTableBulkActions(): array
    {
        return [
            ExportBulkAction::make()
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('Since')
                ->form([
                    DatePicker::make('date')
                        ->default(now()),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['date']) {
                        return null;
                    }

                    return 'Billed since ' . Carbon::parse($data['date'])->toFormattedDateString();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                    return $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', '>=', $data['date']));
                }),
            Filter::make('Until')
                ->form([
                    DatePicker::make('date')
                        ->default(now()),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['date']) {
                        return null;
                    }

                    return 'Until ' . Carbon::parse($data['date'])->toFormattedDateString();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                    return $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', '<=', $data['date']));
                }),
            Filter::make('Insurance')
                ->form([
                    Select::make('insurance_id')
                        ->label("Insurance")
                        ->options(Insurance::all()->pluck('name', 'id'))
                        ->searchable(),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['insurance_id']) {
                        return null;
                    }

                    return 'Insurance: ' . Insurance::find($data['insurance_id'])?->name;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['insurance_id'])) {
                        $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', fn (Builder $query) => $query->whereRelation('discount', 'insurance_id', $data['insurance_id'])));
                    }
                    return $query;
                }),
            Filter::make('payment_mean')
                ->form([
                    Select::make('payment_mean_id')
                        ->label("Payment Mean")
                        ->options(PaymentMean::all()->pluck('name', 'id'))
                        ->searchable(),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['payment_mean_id']) {
                        return null;
                    }

                    return 'Paid via ' . PaymentMean::find($data['payment_mean_id'])?->name;
                })
                ->query(function (Builder $query, array $data): Builder {
                    // dd($data);
                    if (isset($data['payment_mean_id'])) {
                        $query->where('payment_mean_id', $data['payment_mean_id']);
                    }
                    // dd($query->toBase()->toSql());
                    return $query;
                }),
        ];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => InvoicesReport::getUrl([
            "tableFilters" => array_merge($this->tableFilters, [
                "done_by" => [
                    "done_by" => $record->done_by
                ]
            ])
        ]);
    }
}
