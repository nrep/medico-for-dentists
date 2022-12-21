<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceDayNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Invoice::lazy() as $invoice) {
            foreach ($invoice->days as $key => $day) {
                $day->number = $key + 1;
                $day->save();
            }
        }
    }
}
