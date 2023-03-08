<?php

namespace App\Imports;

use App\Models\Charge;
use Maatwebsite\Excel\Concerns\ToModel;

class ChargeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row[6])) {
            return null;
        }

        return new Charge([
            "charge_list_charge_type_id" => 436,
            "name" => $row[4],
            "price" => $row[6],
            "valid_since" => date('Y-m-d'),
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s'),
        ]);
    }
}
