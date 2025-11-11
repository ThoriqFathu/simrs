<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Bangsal;

class AplicareUpdateTT extends Command
{
    /**
     * Nama command untuk dijalankan di CLI
     */
    protected $signature = 'aplicare:update_tt';

    /**
     * Deskripsi command
     */
    protected $description = 'Kirim data tempat tidur (TT) ke API Aplicare BPJS';

    /**
     * Jalankan perintah
     */
    public function handle()
    {
        $this->info("=== Mulai update TT ke BPJS ===");

        $consId     = env('BPJS_CONS_ID');
        $secretKey  = env('BPJS_SECRET_KEY');
        $userKey    = env('BPJS_USER_KEY');

        $tStamp = strval(time());

        $signature = base64_encode(
            hash_hmac('sha256', $consId . "&" . $tStamp, $secretKey, true)
        );

        $this->info("Signature berhasil dibuat");

        $bangsal = Bangsal::with(['kamar' => function($q) {
                $q->where('statusdata', '1');
            }])
            ->where('status', '1')
            ->whereNotIn('kd_bangsal', [
                'AP', 'B0031', 'FAR02', 'GD', 'ICU.U', 'LAB', 'OK', 'IGD', 'ICU',
                'ISOL', 'RR', 'PRE', 'NIF', 'NICU', 'PIC', 'PICU', 'PER', 'VK',
                'STMY', 'STH'
            ])
            ->where('nm_bangsal', 'not like', '%(G)%')
            ->whereHas('kamar', function($q) {
                $q->where('statusdata', '1');
            })
            ->get();

        $hasil = $bangsal->map(function ($item) {
            $total = $item->kamar->count();
            $kosong = $item->kamar->where('status', 'KOSONG')->count();
            $kelas = optional($item->kamar->first())->kelas;
            $kodeKelas = $kelas ? 'KL' . preg_replace('/[^0-9A-Za-z]/', '', str_replace('Kelas', '', $kelas)) : null;

            return [
                "kodekelas" => $kodeKelas,
                "koderuang" => $item->kd_bangsal,
                "namaruang" => $item->nm_bangsal,
                "kapasitas" => (string)$total,
                "tersedia" => (string)$kosong,
                "tersediapria" => "0",
                "tersediawanita" => (string)$kosong,
                "tersediapriawanita" => "0"
            ];
        });

        $url = "https://new-api.bpjs-kesehatan.go.id/aplicaresws/rest/bed/update/0206R007";
        $results = [];

        $this->info("Total data yang akan dikirim: " . count($hasil));

        foreach ($hasil as $index => $data) {
            $this->line("Mengirim data ke-$index => {$data['namaruang']} ...");

            $response = Http::retry(3, 300)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'X-cons-id' => $consId,
                    'X-timestamp' => $tStamp,
                    'X-signature' => $signature,
                    'user_key' => $userKey,
                    'Content-Type' => 'application/json',
                ])
                ->acceptJson()
                ->post($url, $data);

            $status = $response->status();
            $res = $response->json();

            $results[] = [
                'payload' => $data,
                'status' => $status,
                'response' => $res,
            ];

            $this->line("Status: $status - Pesan: " . ($res['metadata']['message'] ?? ''));
            usleep(500000); // jeda 0.5 detik
        }

        $this->info("=== Selesai kirim TT ke BPJS ===");
        $this->info("Total dikirim: " . count($hasil));

        return Command::SUCCESS;
    }
}
