<?php

namespace App\Exports;

use App\Models\Insurance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InsurancesReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected $records;
    protected $filters;

    public function __construct($records, $filters)
    {
        $this->records = $records;
        $this->filters = $filters;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $array = [];

        foreach ($this->records as $insurance) {
            $currentSessions = $insurance->sessions()
                ->whereDate('date', '>=', $this->filters['since'])
                ->whereDate('date', '<=', $this->filters['until'])
                ->whereHas('invoice');
            $subArray = [
                $insurance->name,
                $currentSessions->count()
            ];

            $overallTotal = $overallDiscountedTotal = $overallPatientPays = 0;
            foreach ($currentSessions->get() as $session) {
                $discount = $session?->invoice->discount->discount > 0 ? $session?->invoice->discount->discount / 100 : $session?->invoice->discount->discount;
                $total = $session?->invoice?->charges()->sum('total_price');
                $discountedTotal = $total * $discount;
                $patientPays = $total - $discountedTotal;

                $overallTotal += $total;
                $overallDiscountedTotal += $discountedTotal;
                $overallPatientPays += $patientPays;
            }

            $subArray[] = $overallTotal;
            $subArray[] = round($overallDiscountedTotal);
            $subArray[] = round($overallPatientPays);

            $array[] = $subArray;
        }

        $collection = new Collection($array);

        return $collection;
    }

    public function headings(): array
    {
        $array = [
            'Name',
            'Number of Bills',
            'Total',
            'Insurance pays',
            'Patient pays',
        ];

        return $array;
    }
}
