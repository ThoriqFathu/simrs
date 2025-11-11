<?php

namespace App\Http\Controllers;

use App\Models\Bangsal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SirsController extends Controller
{
    public function index()
    {
//         $consId     = env('BPJS_CONS_ID');
//         $secretKey  = env('BPJS_SECRET_KEY');
//         $userKey    = env('BPJS_USER_KEY');

//         $tStamp = strval(time());

//         $signature = base64_encode(
//             hash_hmac('sha256', $consId . "&" . $tStamp, $secretKey, true)
//         );
//      dd($signature, $tStamp);   

//     $bangsal = Bangsal::with(['kamar' => function($q) {
//         $q->where('statusdata', '1'); // filter kamar
//     }])
//     ->where('status', '1') // filter bangsal
//     ->whereNotIn('kd_bangsal', ['AP', 'B0031', 'FAR02', 'GD', 'ICU.U', 'LAB', 'OK', 'IGD', 'ICU', 'ISOL', 'RR', 'PRE', 'NIF', 'NICU', 'PIC', 'PICU', 'PER', 'VK', 'STMY', 'STH'])
//             ->where('nm_bangsal', 'not like', '%(G)%')
//     ->whereHas('kamar', function($q) {
//         $q->where('statusdata', '1'); // pastikan bangsal punya kamar aktif
//     })
//     ->get();
//     // dd(json_decode($bangsal));
//         $hasil = $bangsal->map(function ($item) {
//             $total = $item->kamar->count();
//             $kosong = $item->kamar->where('status', 'KOSONG')->count();
//             $isi = $item->kamar->where('status', 'ISI')->count();
//             $kelas = optional($item->kamar->first())->kelas;

//             // Ubah "Kelas 1" jadi "KL1" (hapus kata "Kelas " dan ganti awalan jadi KL)
//              $kodeKelas = $kelas ? 'KL' . preg_replace('/[^0-9A-Za-z]/', '', str_replace('Kelas', '', $kelas)) : null;
//             return [
//                 "kodekelas" => $kodeKelas,
//                 "koderuang" => $item->kd_bangsal,
//                 "namaruang" => $item->nm_bangsal,
//                 "kapasitas" => (string)$total,
//                 "tersedia" => (string)$kosong,
//                 "tersediapria" => "0",
//                 "tersediawanita" => (string)$kosong, // opsional, bisa kamu sesuaikan
//                 "tersediapriawanita" => "0"
//             ];
//         });

//         // dd(json_decode($hasil));
//         // $payload = $hasil->values()->toArray(); 

//         $url = "https://new-api.bpjs-kesehatan.go.id/aplicaresws/rest/bed/update/0206R007";

// $results = [];

//         // Kirim satu per satu
//         foreach ($hasil as $data) {
//             $response = Http::retry(3, 300) // max 3x retry, delay 300ms
//                 ->withOptions(['verify' => false])
//                 ->withHeaders([
//                     'X-cons-id' => $consId,
//                     'X-timestamp' => $tStamp,
//                     'X-signature' => $signature,
//                     'user_key' => $userKey,
//                     'Content-Type' => 'application/json',
//                 ])
//                 ->acceptJson()
//                 ->post($url, $data);


//             $results[] = [
//                 'payload' => $data,
//                 'status' => $response->status(),
//                 'response' => $response->json(),
//             ];

//             usleep(500000); // jeda 0.5 detik per request
//         }


//         return response()->json([
//             'total_dikirim' => count($hasil),
//             'hasil' => $results
//         ]);
 
    }
    
}

        