<?php

namespace App\Exports;

use App\Models\File;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FilesExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return File::all();
    }

    public function map($file): array
    {
        $array = [
            sprintf("%05d", $file->number) . '/' . $file->registration_year,
            $file->names,
        ];
        
        $insurances = "";
        $affiliationNumber = "";

        foreach ($file->linkedInsurances as $key => $linkedInsurance) {
            $affNo = "";

            if ($linkedInsurance->insurance_id == 4) {
                $affNo = $linkedInsurance->specific_data['member_number'];
            } else if ($linkedInsurance->insurance_id == 6 || $linkedInsurance->insurance_id == 9 || $linkedInsurance->insurance_id == 12 || $linkedInsurance->insurance_id == 13 || $linkedInsurance->insurance_id == 14 || $linkedInsurance->insurance_id == 15) {
                $affNo = $linkedInsurance->specific_data['affiliation_number'];
            } else if ($linkedInsurance->insurance_id == 7) {
                $affNo = $linkedInsurance->specific_data['police_number'];
            } else if ($linkedInsurance->insurance_id == 11) {
                $affNo = $linkedInsurance->specific_data['member_number'];
            }

            if ($key == 0) {
                $insurances = $linkedInsurance->insurance->name;
                $affiliationNumber = $affNo;
            } else {
                $insurances .= ", " . $linkedInsurance->insurance->name;
                $affiliationNumber .= ", " . $affNo;
            }
        }

        $array[] = $insurances;
        $array[] = $affiliationNumber;

        return $array;
    }

    public function headings(): array
    {
        return [
            'File Number',
            'Name',
            'Affiliation Number',
        ];
    }
}
