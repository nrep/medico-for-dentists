<?php

namespace App\Console\Commands;

use App\Exports\FilesExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ExportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Excel::store(new FilesExport, 'files.xlsx');
    }
}
