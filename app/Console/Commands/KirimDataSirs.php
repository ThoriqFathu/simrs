<?php

namespace App\Console\Commands;

use App\Models\Bangsal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KirimDataSirs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sirs:update_tt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim data tempat tidur ke SIRS secara otomatis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bangsal = Bangsal::with('kamar')
            ->where('status', '1')
            ->whereNotIn('kd_bangsal', ['AP', 'B0031', 'FAR02', 'GD', 'ICU.U', 'LAB', 'OK', 'IGD'])
            ->where('nm_bangsal', 'not like', '%(G)%')
            ->get();

        $hasil = $bangsal->map(function ($item) {
            $total = $item->kamar->count();
            $kosong = $item->kamar->where('status', 'KOSONG')->count();
            $isi = $total - $kosong;

            return [
                'kd_bangsal' => $item->kd_bangsal,
                'nm_bangsal' => $item->nm_bangsal,
                'total_kamar' => $total,
                'kamar_kosong' => $kosong,
                'kamar_isi' => $isi,
            ];
        });

        // dd($hasil);
        $headers = [
                'X-rs-id' => env('SIRS_RS_ID'),
                'X-Timestamp' => round(microtime(true) * 1000),
                'X-pass' => env('SIRS_PASS'),
                    ];
                    
        $url = env('SIRS_BASE_URL');

        $response = Http::withHeaders($headers)->get($url);
        $dataApi = $response->json()['fasyankes'] ?? [];
        // dd($dataApi);
        // dd($response->json());
        $matches = [];

        foreach ($hasil as $item) {
            $ruang = strtolower(trim($item['nm_bangsal']));

            $dataSiranap = collect($dataApi)->first(function ($api) use ($ruang) {
                $apiRuang = strtolower(trim($api['ruang'] ?? ''));
                return $apiRuang === $ruang;
            });

            // ✅ Hanya tambahkan kalau ditemukan match
            if ($dataSiranap) {
                $matches[] = [
                    'id_tt' =>$dataSiranap['id_tt'],
                    'bangsal' => $item['nm_bangsal'],
                    'match_api' => $dataSiranap['ruang'],
                    'isi' => $item['kamar_kosong'],
                    'kosong' => $item['kamar_isi'],
                ];
            }
        }

        // dd($matches);

        foreach ($matches as $item) {
            $payload = [
                "id_tt" => $item['id_tt'], // bisa disesuaikan, kalau ada id dari API bisa pakai itu
                "ruang" => $item['bangsal'],
                "jumlah_ruang" => "0",
                "jumlah" => (string) $item['kosong'], // total kamar kosong
                "terpakai" => (string) $item['isi'], // kamar terpakai
                "terpakai_suspek" => "0",
                "terpakai_konfirmasi" => "0",
                "antrian" => "0",
                "prepare" => "0",
                "prepare_plan" => "0",
                "covid" => 0,
                "terpakai_dbd" => "0",
                "terpakai_dbd_anak" => "0",
                "jumlah_dbd" => "0",
            ];

        $response = Http::withHeaders($headers)->put($url, $payload);
        
        if ($response->failed()) {
                Log::error('Gagal kirim data untuk ruang: ' . $item['bangsal'], [
                    'response' => $response->body(),
                ]);
            }
        else{
            Log::info('✅ Berhasil kirim data ruang: ' . $item['bangsal']);
            }
        }
        $this->info('Data SIRS berhasil dikirim!');
    }
}
