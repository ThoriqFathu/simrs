<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SinkronSepController extends Controller
{
    public function index(Request $request)
    {
        ini_set('max_execution_time', 300);
        $tanggalAwal  = $request->get('tanggal_awal', date('Y-m-d'));
        $tanggalAkhir = $request->get('tanggal_akhir', date('Y-m-d'));

        $data = DB::table('bridging_sep')
            ->leftJoin('pegawai', 'bridging_sep.user', '=', 'pegawai.nik')
            ->whereBetween('bridging_sep.tglsep', [$tanggalAwal, $tanggalAkhir])
            ->select('pegawai.nama', 'bridging_sep.*')
            ->orderBy('bridging_sep.tglsep', 'desc')
            ->get();

        $cacheFile = storage_path('app/synced-sep.json');
        if (! file_exists($cacheFile)) {
            file_put_contents($cacheFile, '{}');
        }

        $cachedSep = json_decode(file_get_contents($cacheFile), true);

        $rekap = [];
        foreach ($data as $item) {
            $no_sep = $item->no_sep;

            if (isset($cachedSep[$no_sep])) {
                $item->sinkron = $cachedSep[$no_sep];
            } else {
                $get_sep            = get_sep_bpjs($no_sep);
                $item->sinkron      = $get_sep === null ? 0 : 1;
                $cachedSep[$no_sep] = $item->sinkron;

                // langsung simpan saat loop
                file_put_contents($cacheFile, json_encode($cachedSep, JSON_PRETTY_PRINT), LOCK_EX);
            }
            $item->jnspelayanan = $item->jnspelayanan == 1 ? 'Ranap' : 'Ralan';
            // if ($item->sinkron == 0) {
            // }
            $rekap[] = (array) $item;
        }

// generate header dinamis
        $allKeys = [];
        foreach ($rekap as $row) {
            $allKeys = array_merge($allKeys, array_keys($row));
        }
        $allKeys = array_unique($allKeys);

        return view('sinkron-sep.index', [
            'flattened'    => $rekap,
            'allKeys'      => $allKeys,
            'tanggalAwal'  => $tanggalAwal,
            'tanggalAkhir' => $tanggalAkhir,
        ]);

    }
}
