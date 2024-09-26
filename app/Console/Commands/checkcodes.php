<?php

namespace App\Console\Commands;

use App\Models\ConfirmationCode;
use Illuminate\Console\Command;

class checkcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:checkcodes';

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
        $confirmationCodes = ConfirmationCode::all();
        foreach ($confirmationCodes  as $confirmationCode) {
            if (now()->diffInHours($confirmationCode->created_at) >= 1) {
                $confirmationCode->delete();
            }
        }
    }
}
