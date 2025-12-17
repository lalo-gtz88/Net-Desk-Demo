<?php

namespace App\Console;


use App\Jobs\SendTelegramMonitoreo;
use App\Models\Endpoint;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */


    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            // --- Demo endpoints simulados ---
            $demoEndpoints = collect([
                (object)[
                    'ip' => '192.168.0.1',
                    'status' => 'up',
                    'last_status' => 'down',
                    'fails_count' => 1
                ],
                (object)[
                    'ip' => '192.168.0.2',
                    'status' => 'down',
                    'last_status' => 'up',
                    'fails_count' => 0
                ],
                (object)[
                    'ip' => '192.168.0.3',
                    'status' => 'up',
                    'last_status' => 'up',
                    'fails_count' => 0
                ],
            ]);

            echo "=== NetDesk Demo Monitoring Run ===\n";

            foreach ($demoEndpoints as $ep) {
                // Simular cambio de estado
                $isUpNow = rand(0, 1) ? 'up' : 'down';
                echo "Endpoint {$ep->ip} simulated as {$isUpNow}\n";

                // Lógica demo similar a producción
                if ($isUpNow === 'up') {
                    if ($ep->fails_count > 0) $ep->fails_count = 0;
                    if ($ep->status !== 'up') {
                        $ep->last_status = $ep->status;
                        $ep->status = 'up';
                        // Demo: no guardamos en DB
                        echo "Status changed to UP (demo)\n";
                    }
                } else {
                    $ep->fails_count++;
                    if ($ep->fails_count >= 2 && $ep->status !== 'down') {
                        $ep->last_status = $ep->status;
                        $ep->status = 'down';
                        // Demo: no enviamos notificaciones
                        echo "Status changed to DOWN (demo)\n";
                    }
                }
            }

            echo "=== End of Demo Monitoring Run ===\n";
        })->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
