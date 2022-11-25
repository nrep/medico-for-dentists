<?php

namespace App\Exports;

use App\Models\ChargeType;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoicesExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $invoices = Invoice::whereRelation('session', function (Builder $query) {
            return $query->whereRelation('fileInsurance', 'insurance_id', 5);
        })
            ->whereRelation('charges', function (Builder $query) {
                return $query->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', '!=', 5);
                });
            })
            ->get();

        $chargeTypes = ChargeType::all();

        foreach ($invoices as $invoice) {
            $invoice->numberOfRows = 1;
            foreach ($chargeTypes as $chargeType) {
                $count = $invoice->charges()->whereRelation('charge', fn (Builder $query) => $query->whereRelation('chargeListChargeType', 'charge_type_id', $chargeType->id))->count();
                if ($count > $invoice->numberOfRows) {
                    $invoice->numberOfRows = $count;
                }
            }
        }

        return $invoices;
    }

    public function map($invoice): array
    {
        $array = [];
        for ($i = 0; $i < $invoice->numberOfRows; $i++) {
            $subArray = [];

            if ($i == 0) {
                $subArray[] = $invoice->session->date;
                $subArray[] = "OPD";
                $subArray[] = $invoice->session->fileInsurance->specific_data['affiliation_number'];
                $subArray[] = $invoice->session->fileInsurance->file->names;
                $subArray[] = $invoice->session->fileInsurance->file->sex;
                $subArray[] = $invoice->session->fileInsurance->file->year_of_birth;

                // Category of beneficiary
                // Is affiliated
                $subArray[] = $invoice->session->fileInsurance->specific_data['category_of_beneficiary'] == "Affiliated" ? "v" : "";
                // Is dependent;
                $subArray[] = $invoice->session->fileInsurance->specific_data['category_of_beneficiary'] == "Dependent" ? "v" : "";
                // Consulted by
                $subArray[] = $invoice->days()->first()->consultedBy->names;
                // Doctor level
                $subArray[] = $invoice->days()->first()->consultedBy?->specific_data ? $invoice->days()->first()->consultedBy?->specific_data['Qualification'] : '';
                // Consultation Fees
                $subArray[] = $invoice->charges()
                    ->whereRelation('charge', function (Builder $query) {
                        return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                    })
                    ->sum('total_price');
            } else {
                $subArray[] = '';
                $subArray[] = "";
                $subArray[] = '';
                $subArray[] = '';
                $subArray[] = '';
                $subArray[] = '';

                // Category of beneficiary
                // Is affiliated
                $subArray[] = "";
                // Is dependent;
                $subArray[] = "";
                // Consulted by
                $subArray[] = '';
                // Doctor level
                $subArray[] = '';
                // Consultation Fees
                $subArray[] = '';
            }

            // Laboratory Exams
            // Exam
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            // Imaging
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 28);
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            // Surgery
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', fn (Builder $query) => $query->whereIn('charge_type_id', [10, 24]));
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            // Acts
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', fn (Builder $query) => $query->whereNotIn('charge_type_id', [1, 2, 28, 10, 24, 4, 3]));
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            // Consummables
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 4);
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            // Drugs
            $charges = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                });
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]->charge?->name : '';
            // Number
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->quantity : '';
            // Amount
            $subArray[] = isset($charges->get()[$i]) ? $charges->get()[$i]?->total_price : '';

            $totalPrice = $invoice->charges()->sum('total_price');

            if ($i == 0) {
                $subArray[] = $totalPrice;

                $subArray[] = $totalPrice > 0 ? round($totalPrice * ($invoice->session->discount->discount / 100)) : '';
            }

            $array[] = $subArray;
        }

        return $array;
    }

    public function headings(): array
    {
        return [
            'DATE',
            'DEPARTMENT',
            'Affiliation No',
            'Names',
            'Sex',
            'AGE',
            'Affiliated',
            'Dependent',
            'Consultation',
            'Consulting Dr/Level',
            'CONSULTATION FEES',
            'EXAM',
            'NUMBER',
            'AMOUNT',
            'TYPES',
            'NUMBER',
            'AMOUNT',
            'TYPES',
            'NUMBER',
            'AMOUNT',
            'TYPES',
            'NUMBER',
            'AMOUNT',
            'TYPES',
            'NUMBER',
            'AMOUNT',
            'TYPES',
            'NUMBER',
            'AMOUNT',
            'TOTAL 100%',
            'TOTAL 85%'
        ];
    }
}
