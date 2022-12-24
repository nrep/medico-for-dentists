<?php

namespace App\Filament\Pages;

use App\Exports\InvoiceReportExport;
use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use App\Models\Employee;
use App\Models\Insurance;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentMean;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Layout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class InvoicesReport extends Page implements HasTable
{
    use InteractsWithTable;

    // protected static ?string $navigationIcon = 'tabler-report-analytics';

    protected static string $view = 'filament.pages.invoices-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Bills';

    protected $queryString = [
        'tableFilters',
        'tableSortColumn',
        'tableSortDirection',
        'tableSearchQuery' => ['except' => ''],
    ];

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('Cashier') && !auth()->user()->hasAnyRole(['Admin', 'Data Manager']);
    }

    protected function getTableQuery(): Builder
    {
        return InvoicePayment::select(
            'invoice_days.invoice_id',
            DB::raw('SUM(DISTINCT(invoice_payments.amount)) AS paid'),
            DB::raw('GROUP_CONCAT(users.name) AS paid_to'),
            DB::raw('GROUP_CONCAT(u.name) AS collaborators'),
            DB::raw('GROUP_CONCAT(employees.names) AS consulted_by')
        )
            ->join('users', 'invoice_payments.done_by', 'users.id')
            ->join('invoice_days', 'invoice_payments.invoice_id', 'invoice_days.invoice_id')
            ->join('invoice_items', 'invoice_days.id', 'invoice_items.invoice_day_id')
            ->join('users AS u', 'invoice_items.done_by', 'u.id')
            ->join('employees', 'invoice_days.doctor_id', 'employees.id')
            ->when(auth()->user()->hasRole('Cashier') && !auth()->user()->hasAnyRole(['Admin', 'Data Manager']), fn (Builder $query) => $query->where('invoice_payments.done_by', auth()->id()))
            ->groupBy('invoice_days.invoice_id');
    }

    protected function getTableColumns(): array
    {
        $columns = [
            TextColumn::make('invoice.session_id')
                ->label('Invoice number')
                ->searchable()
                ->sortable()
                ->formatStateUsing(fn ($state) => "PROV-" . sprintf("%06d", $state)),
            TextColumn::make('invoice.session.fileInsurance.file.number')
                ->formatStateUsing(fn (InvoicePayment $record) => sprintf("%04d", $record->invoice->session->fileInsurance->file->number) . "/" . $record->invoice->session->fileInsurance->file->registration_year)
                ->label('File number')
                ->searchable()
                ->sortable(),
            TextColumn::make('invoice.session.fileInsurance.file.names')
                ->label('Patient Names')
                ->searchable()
                ->sortable()
                ->wrap(),
            TextColumn::make('invoice.session.fileInsurance.insurance.name')
                ->searchable()
                ->sortable(),
            TextColumn::make('total_amount')
                ->label('Total Amount')
                ->formatStateUsing(function (InvoicePayment $record) {
                    $totalAmount = 0;

                    foreach ($record->invoice->charges as $key => $charge) {
                        $totalAmount += $charge->totalPrice;
                    }

                    return $totalAmount;
                }),
            TextColumn::make('insurance_pays')
                ->label('Insurance')
                ->formatStateUsing(function (InvoicePayment $record) {
                    $totalAmount = $insuracePays = 0;

                    foreach ($record->invoice->charges as $key => $charge) {
                        $totalAmount += $charge->totalPrice;
                    }

                    if ($totalAmount > 0) {
                        $insuracePays = $totalAmount * $record->invoice->session->discount->discount / 100;
                    }

                    return round($insuracePays);
                })
                ->searchable(),
            TextColumn::make('patient_pays')
                ->label('Patient')
                ->getStateUsing(function (InvoicePayment $record) {
                    $totalAmount = $patientPays = 0;

                    foreach ($record->invoice->charges as $key => $charge) {
                        $totalAmount += $charge->totalPrice;
                    }

                    if ($totalAmount > 0) {
                        $patientPays = $totalAmount * (100 - $record->invoice->session->discount->discount) / 100;
                    }

                    return round($patientPays);
                })
                ->searchable(),
            TextColumn::make('paid')
                ->label('Paid')
                ->searchable(),
            TextColumn::make('paid_vs_patient_pays')
                ->label('Difference')
                ->getStateUsing(function (InvoicePayment $record) {
                    $totalAmount = $patientPays = 0;

                    foreach ($record->invoice->charges as $key => $charge) {
                        $totalAmount += $charge->totalPrice;
                    }

                    if ($totalAmount > 0) {
                        $patientPays = $totalAmount * (100 - $record->invoice->session->discount->discount) / 100;
                    }

                    return round($record->paid - $patientPays);
                })
                ->searchable()
                ->toggledHiddenByDefault(),
            TextColumn::make('invoice.session.recordedBy.name')
                ->toggledHiddenByDefault(),
            TagsColumn::make('consulted_by')
                ->separator(',')
                ->toggledHiddenByDefault(),
            TagsColumn::make('paid_to')
                ->separator(',')
                ->toggledHiddenByDefault(),
            TagsColumn::make('collaborators')
                ->separator(',')
                ->toggledHiddenByDefault(),
        ];

        $paymentMeans = PaymentMean::all();

        foreach ($paymentMeans as $paymentMean) {
            $columns[] = TextColumn::make($paymentMean->name)
                ->label($paymentMean->name)
                ->getStateUsing(fn (InvoicePayment $record) => $record->invoice->payments()->where('payment_mean_id', $paymentMean->id)->sum('amount'))
                ->toggledHiddenByDefault();
        }

        return $columns;
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('date')
                ->form([
                    DatePicker::make('date')
                        ->default(now()),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['date']) {
                        return null;
                    }

                    return 'Received on ' . Carbon::parse($data['date'])->toFormattedDateString();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                    return $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', $data['date']));
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
            Filter::make('done_by')
                ->form([
                    Select::make('done_by')
                        ->label("Done By")
                        ->options(User::whereRelation('roles', 'name', 'Cashier')->pluck('name', 'id'))
                        ->searchable()
                        ->default(auth()->user()->hasRole("Cashier") ? auth()->id() : null),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['done_by']) {
                        return null;
                    }

                    return 'Paid to ' . User::find($data['done_by'])?->name;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['done_by'])) {
                        $query->where('invoice_payments.done_by', $data['done_by']);
                    }
                    return $query;
                })
                ->hidden(!auth()->user()->hasAnyRole(["Admin", "Data Manager"])),
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
                    if (isset($data['payment_mean_id'])) {
                        $query->where('payment_mean_id', $data['payment_mean_id']);
                    }
                    return $query;
                }),
            Filter::make('doctor')
                ->form([
                    Select::make('doctor_id')
                        ->label("Doctor")
                        ->options(Employee::whereRelation(
                            'categories',
                            fn (Builder $query) => $query->whereRelation('category', 'name', 'Doctor')
                        )->get()->pluck('names', 'id'))
                        ->searchable(),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if (!$data['doctor_id']) {
                        return null;
                    }

                    return 'Consulted by ' . Employee::find($data['doctor_id'])?->names;
                })
                ->query(function (Builder $query, array $data): Builder {
                    if (isset($data['doctor_id'])) {
                        $query->whereRelation(
                            'invoice',
                            fn (Builder $query) => $query->whereRelation('days', 'doctor_id', $data['doctor_id'])
                        );
                    }
                    return $query;
                })
                ->columnSpan(2)
        ];
    }

    public function export()
    {
        return Excel::download(new InvoicesExport('OPD', $this->tableFilters), 'invoices.xlsx');
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100, -1];
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => InvoiceResource::getUrl('view', $record->invoice_id);
    }

    public function getTableRecordKey(Model $record): string
    {
        return $record->invoice_id;
    }

    protected function getTableFiltersFormColumns(): int
    {
        return 2;
    }

    public function getActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->action(function () {
                    $doneBy = $this->tableFilters['done_by']['done_by'];
                    $userName = User::find($doneBy)?->name;
                    $date = $this->tableFilters["date"]["date"];
                    $date = Carbon::parse($date)->toFormattedDateString();
                    return Excel::download(new InvoiceReportExport($this->tableFilters), "Patients billed by " . $userName . " on " . $date . ".xlsx");
                })
                ->icon('heroicon-s-download')
        ];
    }
}
