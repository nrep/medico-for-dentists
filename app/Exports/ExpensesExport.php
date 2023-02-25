<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;    
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $number = 0;

        foreach ($this->expenses as $key => $expense) {
            $number++;
            $expense->number = $number;
        }

        return $this->expenses;
    }

    public function headings(): array
    {
        $array = [
            'No',
            'Bill No',
            'Account',
            'Payment Mean',
            'Receiver',
            'Date',
            'Amount',
            'Line',
            'Reasons',
            'EBM',
        ];

        return $array;
    }

    public function map($expense): array {
        $map = [];

        $map[] = $expense->number;
        $map[] = $expense->bill_no;
        $map[] = $expense->account->name;
        $map[] = $expense->paymentMean->name;
        $map[] = $expense->expenseable?->names ?? $expense->expenseable?->name;
        $map[] = $expense->date;
        
        $budgetLines = "";
        $ebmNumbers = "";
        $amount = 0;
        $reasons = "";

        foreach ($expense->items as $key => $item) {
            $budgetLines .= $item->line->name;
            $ebmNumbers .= $item->ebm_bill_number;
            $amount += $item->amount;
            $reasons .= $item->reason;

            if ($key != count($expense->items) - 1) {
                $budgetLines .= ', ';
                $ebmNumbers .= ', ';
                $reasons .= ', ';
            }
        }

        $map[] = $amount;
        $map[] = $budgetLines;
        $map[] = $reasons;
        $map[] = $ebmNumbers;

        return $map;
    }
}
