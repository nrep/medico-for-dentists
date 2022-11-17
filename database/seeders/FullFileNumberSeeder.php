<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FullFileNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (File::lazy() as $file) {
            $file->full_number = sprintf("%05d", substr($file->number, 0)) . "/" . $file->registration_year;
            $file->save();
        }
    }
}
