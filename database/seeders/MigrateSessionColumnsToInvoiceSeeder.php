<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateSessionColumnsToInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Session::lazy() as $session) {
            if ($session->invoice) {
                $invoice = $session->invoice;

                $invoice->discount_id = $session->discount_id;
                $invoice->specific_data = $session->specific_data;

                $invoice->save();
            }
        }
    }
}
