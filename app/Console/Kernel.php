<?php

namespace App\Console;

use App\Library\PineConeClient;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Library\Requester;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
    
        $schedule->call(function (){

            $pinecone = new PineConeClient();
            $isIndex = $pinecone->getIndexDetails();

            if(!$isIndex){

                info('index not found, creating.');

                $pinecone->createIndex();
                info('index created');

                $requester = new Requester();
                $requester->run();
            }
            else{
                $requester = new Requester();
                $requester->run();
            }

           
        })->name('cron')->withoutOverlapping()->everyTwoMinutes();

        $schedule->call(function () {

            $pinecone = new PineConeClient();
            $d = $pinecone->getIndexDetails();

        })->daily();

        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
