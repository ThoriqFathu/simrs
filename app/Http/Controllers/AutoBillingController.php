<?php
namespace App\Http\Controllers;

use App\Models\RegPeriksa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AutoBillingController extends Controller
{
    public function index(Request $request)
    {

        $tglAwal   = request('tgl_awal') ? Carbon::parse(request('tgl_awal')) : now();
        $tglAkhir  = request('tgl_akhir') ? Carbon::parse(request('tgl_akhir')) : now();
        $list_data = RegPeriksa::where('kd_pj', 'BPJ')
            ->where('status_lanjut', 'Ralan')
            ->where('stts', 'Sudah')
            ->where('kd_poli', '!=', 'IGDK')
            ->whereBetween('tgl_registrasi', [$tglAwal, $tglAkhir])
            ->get();
        // dd(json_decode($list_data));
        return view('auto-billing.index', compact('list_data'));
    }

    public function store_all(Request $request)
    {
        $tglAwal  = $request->input('tgl_awal');
        $tglAkhir = $request->input('tgl_akhir');

        $list_data = RegPeriksa::where('kd_pj', 'BPJ')
            ->where('status_lanjut', 'Ralan')
            ->where('stts', 'Sudah')
            ->where('kd_poli', '!=', 'IGDK')
            ->whereBetween('tgl_registrasi', [$tglAwal, $tglAkhir])
            ->get();

        $result = [
            'updated' => 0,
            'errors'  => 0,
            'failed'  => [],
        ];
        // dd(json_decode($list_data));
        foreach ($list_data as $data) {
            try {
                RegPeriksa::where('no_rawat', $data->no_rawat)->update([
                    'status_bayar' => 'Sudah Bayar',
                ]);
                $result['updated']++;
            } catch (\Throwable $e) {
                Log::error('Gagal update status_bayar', [
                    'no_rawat' => $data->no_rawat,
                    'error'    => $e->getMessage(),
                ]);
                $result['errors']++;
                $result['failed'][] = $data->no_rawat;
            }
        }

        // Kembalikan respon sesuai variabel di atas
        return back()->with(
            'status',
            "Updated: {$result['updated']}, Error: {$result['errors']}"
        );
    }
}
