<?php

namespace App\Exports;

use App\Models\ChargeType;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoicesExport implements FromCollection, WithMapping, ShouldAutoSize, WithHeadings
{
    public $filters;
    public $department;
    public $insuranceId;

    public function __construct(array $filters, int $insuranceId = 5, string $department = "OPD")
    {
        // dd($filters, $insuranceId);
        $this->department = $department;
        $this->filters = $filters;
        $this->insuranceId = $insuranceId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $department = $this->department;
        $insuranceId = $this->insuranceId;
        $invoices = Invoice::whereRelation('session', function (Builder $query) {
            return $query->whereRelation('fileInsurance', 'insurance_id', $this->insuranceId)
                ->whereDate('date', '>=', Carbon::parse($this->filters['period']['since'])->format('Y-m-d'))
                ->whereDate('date', '<=', Carbon::parse($this->filters['period']['until'])->format('Y-m-d'))
                ->orderBy('date', 'ASC');
        })
            ->whereRelation('charges', function (Builder $query) use ($department, $insuranceId) {
                return $query->whereRelation('charge', function (Builder $query) use ($department, $insuranceId) {
                    if ($insuranceId == 5) {
                        if ($department == "OPD") {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', '!=', 5);
                        } else if ($department == "IPD") {
                            return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                        }
                    }
                });
            })
            ->orderBy('id', 'ASC')
            ->get();

        $chargeTypes = ChargeType::all();

        $number = 0;
        foreach ($invoices as $invoice) {
            $number += 1;
            $invoice->number = $number;
            if ($this->insuranceId == 5) {
                $invoice->numberOfRows = 1;
                foreach ($chargeTypes as $chargeType) {
                    $count = $invoice->charges()->whereRelation('charge', fn (Builder $query) => $query->whereRelation('chargeListChargeType', 'charge_type_id', $chargeType->id))->count();
                    if ($count > $invoice->numberOfRows) {
                        $invoice->numberOfRows = $count;
                    }
                }
            }
        }

        return $invoices;
    }

    public function map($invoice): array
    {
        $array = [];

        $total = $invoice->charges()
            ->sum('total_price');

        if ($this->insuranceId == 5) {
            for ($i = 0; $i < $invoice->numberOfRows; $i++) {
                $subArray = [];

                if ($i == 0) {
                    $subArray[] = $invoice->number;
                    $subArray[] = $invoice->session->date;
                    $subArray[] = $this->department;
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

                    $subArray[] = $totalPrice > 0 ? round($totalPrice * ($invoice->discount->discount / 100)) : '';
                }

                $array[] = $subArray;
            }
        } else if ($this->insuranceId == 4) {
            $array[] = $invoice->number;
            $array[] = $invoice->session->date;
            $array[] = (isset($invoice->specific_data['voucher_number'])) ? "40440006/" . $invoice->specific_data['voucher_number'] . "/" . substr($invoice->session->date, 2, 2) : "";
            $array[] = $invoice->session->fileInsurance->specific_data['member_number'];
            $array[] = $invoice->session->fileInsurance->file->year_of_birth;
            $array[] = $invoice->session->fileInsurance->file->sex;
            $array[] = $invoice->session->fileInsurance->file->names;
            $array[] = $invoice->session->fileInsurance->specific_data['affiliate_name'];
            $array[] = $invoice->session->fileInsurance->specific_data['affiliate_affectation'];
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1)
                        ->whereRaw('name NOT REGEXP "HOSPITAL VISIT"');
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 28);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                })
                ->sum('total_price');

            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                        return $query->whereNotIn('charge_type_id', [1, 2, 28, 5, 3]);
                    })
                        ->orWhereRaw('name REGEXP "HOSPITAL VISIT"');
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                })
                ->sum('total_price');
            $array[] = $total;
            $array[] = $total > 0 ? round($total * ($invoice?->discount->discount > 0 ? $invoice?->discount->discount / 100 : $invoice?->discount->discount)) : 0;
        } else if ($this->insuranceId == 6 || $this->insuranceId == 9) {
            $array[] = $invoice->number;
            $array[] = $invoice->session->date;
            $array[] = $invoice->session->fileInsurance->specific_data['police_number'];
            $array[] = $invoice->session->fileInsurance->specific_data['affiliation_number'];
            $array[] = $invoice->session->fileInsurance->file->names;
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                        return $query->whereNotIn('charge_type_id', [1, 2, 5, 3]);
                    });
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                })
                ->sum('total_price');
            $array[] = $total;
            $array[] = round($total * ($invoice?->discount->discount > 0 ? $invoice?->discount->discount / 100 : $invoice?->discount->discount));
        } else if ($this->insuranceId == 7) {
            $array[] = $invoice->number;
            $array[] = $invoice->session->fileInsurance->specific_data['scheme_name'] ?? "";
            $array[] = $invoice->session->fileInsurance->specific_data['police_number'] ?? "";
            $array[] = $invoice->specific_data['invoice_number'] ?? "";
            $array[] = $invoice->session->fileInsurance->file->specific_data['last_name'] ?? "";
            $array[] = $invoice->session->fileInsurance->file->specific_data['first_name'] ?? "";
            $array[] = $invoice->session->date;
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 1);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 2);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 3);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 28);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', function (Builder $query) {
                        return $query->whereNotIn('charge_type_id', [1, 2, 28, 4, 5]);
                    });
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 4);
                })
                ->sum('total_price');
            $array[] = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                })
                ->sum('total_price');
            $hospitalizationCost = $invoice->charges()
                ->whereRelation('charge', function (Builder $query) {
                    return $query->whereRelation('chargeListChargeType', 'charge_type_id', 5);
                })
                ->sum('total_price');
            $array[] = $hospitalizationCost > 0 ? 'IPD' : 'OPD';
            $array[] = $total;
            $array[] = round($total * ($invoice?->discount->insured_pays != 100 ? $invoice?->discount->insured_pays / 100 : $invoice?->discount->insured_pays));
            $array[] = round($total * ($invoice?->discount->discount > 0 ? $invoice?->discount->discount / 100 : $invoice?->discount->discount));
        }

        return $array;
    }

    public function headings(): array
    {
        $headings = [];
        if ($this->insuranceId == 5) {
            $headings = [
                'No',
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
        } else if ($this->insuranceId == 4) {
            $headings = [
                'No',
                'Date',
                'Voucher Identification',
                "Beneficiary's Affiliation No",
                "Beneficiary's Age",
                "Beneficiary's Sex",
                "Beneficiary's Names",
                "Affiliated's Names",
                "Affiliated's Affectation",
                'Cost For Consultation 100%',
                'Cost For Laboratory Tests 100%',
                'Cost For Medical Imaging 100%',
                'Cost For Hospitalization 100%',
                'Cost For Procedures & Materials 100%',
                'Cost For Medicines 100%',
                'Total Amount 100%',
                'Total Amount 85%'
            ];
        } else if ($this->insuranceId == 6 || $this->insuranceId == 9) {
            $headings = [
                'No',
                'Date',
                'No Police',
                'No Carte',
                'Nom et Prenom Du Malade',
                'Cons. 100%',
                'Examen Comp. 100%',
                'Hosp. 100%',
                'Acte Et Mater 100%',
                'Medic 100%',
                'Total 100%',
                'A Payer Par SORAS'
            ];
        } else if ($this->insuranceId == 7) {
            $headings = [
                'No',
                'Scheme Name',
                'Smart Card Number',
                'Claim Form Number/Invoice Number',
                'Last Name',
                'First Name',
                'Treatment Date',
                'Consultation',
                'Lab Tests',
                'Drugs',
                'Imaging',
                'Procedures',
                'Consumables',
                'Bed Charges',
                'Benefit Type(OP, IP, MAT)',
                'Gross Amount',
                'Copay',
                'Payable by BRITAM'
            ];
        }

        return $headings;
    }
}
