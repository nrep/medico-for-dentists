<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PaymentMean;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoiceReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    public $filters;
    public $payments;

    public function __construct($tableFilters)
    {
        $this->filters = $tableFilters;

        $this->paymentMeans = PaymentMean::all();
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $invoicePayments = InvoicePayment::select(
            'invoice_payments.invoice_id',
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
            ->when($this->filters['date'], function (Builder $query, $data) {
                $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
                return $query->whereRelation('invoice', fn (Builder $query) => $query->whereRelation('session', 'date', $data['date']));
            })
            ->when($this->filters['done_by'], function (Builder $query, array $data): Builder {
                return $query->where('invoice_payments.done_by', $data['done_by']);
            })
            ->when(auth()->user()->hasRole('Cashier') && !auth()->user()->hasAnyRole(['Admin', 'Data Manager']), fn (Builder $query) => $query->where('invoice_payments.done_by', auth()->id()))
            ->groupBy('invoice_days.invoice_id')
            ->get();

        return $invoicePayments;
    }

    public function map($invoicePayment): array
    {
        $array = [];

        $array[] = "PROV-" . sprintf("%06d", $invoicePayment->invoice->session_id);
        $array[] = sprintf("%04d", $invoicePayment->invoice->session->fileInsurance->file->number) . "/" . $invoicePayment->invoice->session->fileInsurance->file->registration_year;
        $array[] = $invoicePayment->invoice->session->fileInsurance->file->names;
        $array[] = $invoicePayment->invoice->session->fileInsurance->insurance->name;

        $totalAmount = $insuracePays = $patientPays = 0;

        foreach ($invoicePayment->invoice->charges as $key => $charge) {
            $totalAmount += $charge->totalPrice;
        }

        if ($totalAmount > 0) {
            $insuracePays = $totalAmount * $invoicePayment->invoice->session->discount->discount / 100;
            $patientPays = $totalAmount * (100 - $invoicePayment->invoice->session->discount->discount) / 100;
        }

        $array[] = $totalAmount;
        $array[] = round($insuracePays);
        $array[] = round($patientPays);
        $array[] = $invoicePayment->paid;
        $array[] = round($invoicePayment->paid - $patientPays);

        foreach ($this->paymentMeans as $paymentMean) {
            $array[] = $invoicePayment->invoice->payments()->where('payment_mean_id', $paymentMean->id)->sum('amount');
        }

        return $array;
    }

    public function headings(): array
    {
        $array = [
            'Invoice number',
            'File number',
            'Patient names',
            'Insurance',
            'Total amount',
            'Insurance pays',
            'Patient pays',
            'Paid',
            'Difference'
        ];

        foreach ($this->paymentMeans as $paymentMean) {
            $array[] = $paymentMean->name;
        }

        return $array;
    }
}