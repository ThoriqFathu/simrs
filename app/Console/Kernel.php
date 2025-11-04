<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Daftar command Artisan yang tersedia.
     */
    protected $commands = [
        \App\Console\Commands\KirimDataSirs::class, // pastikan ini ada
    ];

    /**
     * Definisikan jadwal perintah aplikasi.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalankan command KirimDataSirs setiap menit
        // $schedule->command('sirs:update_tt')->dailyAt('08:00');
        $schedule->command('sirs:update_tt')->everyMinute();
    }

    /**
     * Daftarkan command untuk Artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
